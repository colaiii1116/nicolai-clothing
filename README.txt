Nicolai Clothing

Technology: PHP 8+, HTML5, CSS3, Vanilla JavaScript.

FILES
- config.php: session and site configuration.
- index.php: main responsive homepage and product interactions.
- login_function.php: authentication processing.
- login.php: login form and server-side validation display.
- logout.php: secure session logout.
- function.php: reusable helpers, escaping, CSRF, redirects and auth checks.
- info.php: branded information/about page.
- student.php: protected authenticated student page.
- success.php: successful login page.
- validation.php: reusable server-side validation.
- includes/header.php: document head, branding and navigation.
- includes/footer.php: footer and script loading.
- css/style.css: visual system and responsive layout.
- js/script.js: mobile menu, gallery, quantity and contact interactions.
- assets/products: product and editorial images extracted from the supplied mockup.

LOCAL LOGIN
Email: student@nicolai.local
Password: Nicolai123!

RUN
1. Put the project in a PHP 8+ web server document root.
2. Make sure URL rewriting is not required; the pages use direct .php routes.
3. Open /index.php.

The visual reference is the supplied Nicolai Clothing mockup. Product images are derived from the mockup's embedded assets. Replace demo authentication with a database-backed system before production use.
