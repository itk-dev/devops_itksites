import { defineConfig } from "vite";
import Symfony from "@symfony/reprise/vite";

// https://github.com/symfony/reprise/blob/main/doc/index.rst
export default defineConfig({
  build: {
    rolldownOptions: {
      input: {
        // EasyAdmin JS and CSS (admin.js imports styles/admin.css), loaded by
        // DashboardController for every admin page.
        admin: "./assets/admin.js",
        // Swagger UI, ReDoc and Scalar theme, loaded by the API Platform docs template.
        "api-docs": "./assets/styles/api-docs.css",
      },
    },
  },
  plugins: [
    Symfony({
      copy: [
        // <micro-lighter> loads grammars with import(`./grammars/${language}.js`)
        // relative to the bundle, which Vite leaves as is, so ship them next to
        // it under their own names.
        {
          from: "node_modules/microlighter/dist/grammars",
          to: "grammars",
          pattern: /\.js$/,
          hash: false,
        },
      ],
    }),
  ],
});
