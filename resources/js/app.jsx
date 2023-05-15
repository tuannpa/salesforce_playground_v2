import './bootstrap';
import '../sass/app.scss';
import 'react-toastify/dist/ReactToastify.css';
import React from "react";
import { createRoot } from 'react-dom/client'
import { createInertiaApp } from "@inertiajs/inertia-react";

createInertiaApp({
    resolve: name => {
        const pages = import.meta.glob('./Pages/**/*.jsx', { eager: true })
        return pages[`./Pages/${name}.jsx`]
    },
    setup({ el, App, props }) {
        createRoot(el).render(<App {...props} />)
    },
});
