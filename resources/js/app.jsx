import "../css/app.css";
import "./bootstrap";

import { createInertiaApp } from "@inertiajs/react";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { createRoot } from "react-dom/client";
import BaseLayout from "./Layouts/BaseLayout";

const appName = import.meta.env.VITE_APP_NAME ?? "Laravel";

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: async (name) => {
        const pages = import.meta.glob("./Pages/**/*.jsx");
        const page = await pages[`./Pages/${name}.jsx`]();

        if (!page.default.layout) {
            page.default.layout = (page) => <BaseLayout>{page}</BaseLayout>;
        }

        return page.default;
    },
    setup({ el, App, props }) {
        const root = createRoot(el);
        root.render(<App {...props} />);
    },
    progress: {
        color: "#4B5563",
    },
});
