import http from 'k6/http';
import { check, sleep } from 'k6';

const BASE = __ENV.BASE_URL || 'http://localhost:8080';
const SMOKE = __ENV.SMOKE === '1';

const SEARCH_TERMS = ['a', 'e', 'o', 'flower', 'bouquet', 'bag'];

const STATIC_PAGES = [
  { path: '/', tag: 'home', weight: 30 },
  { path: '/flowers', tag: 'flowers', weight: 12 },
  { path: '/boquets', tag: 'boquets', weight: 12 },
  { path: '/new-featured', tag: 'new-featured', weight: 10 },
  { path: '/accessories', tag: 'accessories', weight: 8 },
  { path: '/bags', tag: 'bags', weight: 7 },
  { path: '/sale', tag: 'sale', weight: 7 },
  { path: '/cart', tag: 'cart', weight: 5 },
  { path: '__product__', tag: 'product', weight: 14 },
  { path: '__search__', tag: 'search', weight: 5 },
];

export const options = {
  stages: SMOKE
    ? [{ duration: '30s', target: 5 }, { duration: '30s', target: 0 }]
    : [
        { duration: '1m', target: 20 },
        { duration: '2m', target: 50 },
        { duration: '2m', target: 100 },
        { duration: '2m', target: 200 },
        { duration: '1m', target: 0 },
      ],
  thresholds: SMOKE
    ? {
        http_req_failed: ['rate<0.05'],
      }
    : {
        http_req_duration: [
          { threshold: 'p(95)<800', abortOnFail: true, delayAbortEval: '30s' },
        ],
        http_req_failed: [
          { threshold: 'rate<0.01', abortOnFail: true, delayAbortEval: '30s' },
        ],
        'http_req_duration{endpoint:home}': ['p(95)<800'],
        'http_req_duration{endpoint:product}': ['p(95)<1000'],
      },
};

export function setup() {
  const slugs = new Set();

  for (const term of SEARCH_TERMS) {
    const res = http.get(`${BASE}/api/products/search?q=${encodeURIComponent(term)}`, {
      tags: { endpoint: 'setup-search' },
    });
    if (res.status !== 200) continue;

    let items = [];
    try {
      items = res.json();
    } catch (e) {
      continue;
    }
    if (!Array.isArray(items)) continue;

    for (const item of items) {
      if (item && item.slug) slugs.add(item.slug);
    }
  }

  const slugList = [...slugs];
  console.log(`setup: discovered ${slugList.length} product slugs`);

  return { slugs: slugList };
}

function pickOne(pool) {
  const total = pool.reduce((sum, entry) => sum + entry.weight, 0);
  let roll = Math.random() * total;
  for (const entry of pool) {
    roll -= entry.weight;
    if (roll <= 0) return entry;
  }
  return pool[pool.length - 1];
}

function buildUrl(entry, slugs) {
  if (entry.path === '__product__') {
    const slug = slugs[Math.floor(Math.random() * slugs.length)];
    return `${BASE}/product/${slug}`;
  }
  if (entry.path === '__search__') {
    const term = SEARCH_TERMS[Math.floor(Math.random() * SEARCH_TERMS.length)];
    return `${BASE}/api/products/search?q=${encodeURIComponent(term)}`;
  }
  return `${BASE}${entry.path}`;
}

function visit(pool, slugs) {
  const entry = pickOne(pool);
  const res = http.get(buildUrl(entry, slugs), { tags: { endpoint: entry.tag } });
  check(res, { 'status 200': (r) => r.status === 200 });
}

export default function (data) {
  const pool = data.slugs.length
    ? STATIC_PAGES
    : STATIC_PAGES.filter((entry) => entry.path !== '__product__');

  visit(pool, data.slugs);
  sleep(1);

  visit(pool, data.slugs);
  sleep(1);
}
