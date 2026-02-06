# WellCare Connect — Frontend (Twig + Tailwind) Template

This project uses **Twig templates + Tailwind CSS** bundled by **Symfony Webpack Encore**.

## Run (frontend assets)

```bash
npm install
npm run dev
```

For production build:

```bash
npm run build
```

## What’s included (so far)

- **Encore-integrated base layout** (no hardcoded `/build/*.css`)
- **Tailwind dark mode** using the `dark` class
- **Persistent theme toggle** (stores `theme=light|dark` in `localStorage`)
- **Auth module “first”**: consistent auth layout + modernized pages
  - Login
  - Forgot password
  - Reset password
  - Register (patient) multi-step
  - Register (professional) multi-step

## Key files

- `templates/base.html.twig`: global shell, assets, header/footer, theme toggle
- `templates/layouts/auth.html.twig`: shared auth page layout
- `templates/auth/*.html.twig`: auth pages
- `assets/app.js`: Alpine + theme store
- `assets/app.css`: Tailwind layers + shared utility classes (`.input`, `.label`, `.surface`)
- `tailwind.config.js`: `darkMode: 'class'` + Flowbite plugin enabled

