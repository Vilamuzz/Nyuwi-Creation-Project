/**
 * Loads the Midtrans Snap JS SDK dynamically if not already loaded.
 */
export const loadSnapSdk = (snapJsUrl, clientKey) => {
    return new Promise((resolve, reject) => {
        if (window.snap) {
            return resolve(window.snap);
        }

        const scriptUrl = snapJsUrl || "https://app.sandbox.midtrans.com/snap/snap.js";
        const existingScript = document.querySelector(`script[src="${scriptUrl}"]`);

        if (existingScript) {
            existingScript.addEventListener("load", () => resolve(window.snap));
            existingScript.addEventListener("error", reject);
            return;
        }

        const script = document.createElement("script");
        script.src = scriptUrl;
        if (clientKey) {
            script.setAttribute("data-client-key", clientKey);
        }
        script.async = true;
        script.onload = () => resolve(window.snap);
        script.onerror = () => reject(new Error("Gagal memuat Midtrans Snap SDK"));
        document.head.appendChild(script);
    });
};

/**
 * Triggers the Snap payment popup with the provided Snap token.
 */
export const triggerSnapPayment = async ({
    snapToken,
    snapJsUrl,
    clientKey,
    onSuccess,
    onPending,
    onError,
    onClose,
}) => {
    try {
        const snap = await loadSnapSdk(snapJsUrl, clientKey);
        snap.pay(snapToken, {
            onSuccess: (result) => {
                if (onSuccess) onSuccess(result);
            },
            onPending: (result) => {
                if (onPending) onPending(result);
            },
            onError: (result) => {
                if (onError) onError(result);
            },
            onClose: () => {
                if (onClose) onClose();
            },
        });
    } catch (err) {
        console.error("Midtrans Snap error:", err);
        if (onError) onError(err);
    }
};
