# GGF Dashboard

**Glitch GTA France** (GGF) Dashboard is the official dashboard used by the GGF team to manage a part of the 
configuration of Apokabot on GGF Discord server (10.000+ members).

This website has been developed using native PHP (with composer), HTML, CSS and JavaScript. It is hosted for free on
[Vercel](https://vercel.com/).

## 🚀 Objectives

The only objective of this website was to provide a simple and fully working dashboard to the GGF team in less than 
10 hours for internal reason. The website is not intended to be used by anyone else than the GGF team and the code has 
not been optimized for performance or security.

*The website may evolve in the future to support more features, to be more user-friendly and to offer interface to
members for some features of the bot.*

## 📦 Installation

1. Requirements
    - PHP 8.2
    - Composer
    - MongoDB database
    - Discord application with OAuth2 authentication
2. Clone the repository
3. Install dependencies with composer
4. Fill the `.env.example` file and rename it to `.env`
5. Run the website with PHP
```bash
php -S localhost:8000 api/index.php
```

## 🎯 Roadmap
- [x] Restrict access to the dashboard to the GGF team thanks to Discord authentication
- [x] Allow the GGF team to manage QNA questions (with category)
- [ ] Link Vercel project to the GitHub repository
- [ ] Support full guild configuration
- [ ] Offer interface to members to track achievements
- [ ] Offer interface to members to track their progress/level on the server
- [ ] Improve code readability and good practices 😅
- [ ] Clean libraries used since the code is from multiple private projects and therefore different versions are used

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
