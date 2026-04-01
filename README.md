<div align="center">

# 🧠 PolyMind

### One chat interface. Multiple AI brains. Always the right one.

![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php)
![Symfony](https://img.shields.io/badge/Symfony-7.4-000000?style=for-the-badge&logo=symfony)
![Python](https://img.shields.io/badge/Python-3.11-3776AB?style=for-the-badge&logo=python)
![FastAPI](https://img.shields.io/badge/FastAPI-0.111-009688?style=for-the-badge&logo=fastapi)
![Vue.js](https://img.shields.io/badge/Vue.js-3-4FC08D?style=for-the-badge&logo=vue.js)
![Docker](https://img.shields.io/badge/Docker-Compose-2496ED?style=for-the-badge&logo=docker)
![CI](https://img.shields.io/github/actions/workflow/status/salim-atalla/polymind/fastapi.yml?style=for-the-badge&label=FastAPI+CI)
![CI](https://img.shields.io/github/actions/workflow/status/salim-atalla/polymind/symfony.yml?style=for-the-badge&label=Symfony+CI)
![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)

</div>

---

## 📖 About

**PolyMind** is an intelligent chat platform that automatically routes your prompt
to the most suitable AI model based on your intent.

Instead of choosing between ChatGPT, Claude, or Gemini yourself —
PolyMind analyzes your request and picks the best one for you, instantly.

| Intent detected            | Model used         |
| -------------------------- | ------------------ |
| ✍️ Text, emails, summaries | ChatGPT (OpenAI)   |
| 💻 Code, debugging         | Claude (Anthropic) |
| 🎨 Image generation        | Gemini (Google)    |
| ❓ General questions       | Best available     |

> **Note:** Requires valid API keys for OpenAI, Anthropic, and/or Google Gemini.
> The app works with at least one active key thanks to the automatic fallback system.

---

## 🏗️ Architecture

```
[Vue.js] → [Symfony API] → [FastAPI Router] → [OpenAI / Claude / Gemini]
```

-   **Vue.js 3** — Glassmorphism chat UI with conversation history
-   **Symfony 7.4 (PHP 8.3)** — REST API, JWT authentication, chat history
-   **FastAPI (Python 3.11)** — AI routing microservice, intent analysis
-   **PostgreSQL 15** — Users, conversations, messages storage
-   **Docker** — Full containerized environment

---

## ✨ Features

-   🤖 **Automatic AI routing** — detects intent and picks the best model
-   🔄 **Fallback system** — if one provider fails, tries the next automatically
-   💬 **Conversation history** — all chats saved and resumable
-   🔐 **JWT authentication** — secure register/login
-   🎨 **Glassmorphism UI** — modern dark theme with animated effects
-   📊 **AI metadata** — shows which model answered and response time
-   📄 **API documentation** — Swagger UI on FastAPI + OpenAPI on Symfony
-   🧪 **Test suites** — PHPUnit (Symfony) + Pytest (FastAPI)
-   🔁 **CI/CD** — GitHub Actions for all three services

---

## 🚀 Getting Started

### Prerequisites

-   Docker & Docker Compose
-   Git
-   API keys: OpenAI, Anthropic, and/or Google Gemini

### Installation

```bash
# 1. Clone the repository
git clone https://github.com/salim-atalla/polymind.git
cd polymind

# 2. Set up FastAPI environment
cp fastapi-router/.env.example fastapi-router/.env
# Edit fastapi-router/.env and add your API keys:
# OPENAI_API_KEY=your_key_here
# ANTHROPIC_API_KEY=your_key_here
# GEMINI_API_KEY=your_key_here

# 3. Set up Symfony environment
cp symfony-app/.env symfony-app/.env.local
# Edit symfony-app/.env.local and update:
# DATABASE_URL, JWT_PASSPHRASE

# 4. Generate JWT keys
cd symfony-app
mkdir -p config/jwt
openssl genrsa -out config/jwt/private.pem 4096
openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem
cd ..

# 5. Start all services
docker compose up -d

# 6. Run Symfony migrations
docker compose exec symfony-app php bin/console doctrine:migrations:migrate
```

### Manual Setup (without Docker)

```bash
# Terminal 1 — Symfony backend
cd symfony-app
php -S localhost:8080 -t public/ public/router.php

# Terminal 2 — FastAPI router
cd fastapi-router
uvicorn app.main:app --reload --port 8000

# Terminal 3 — Vue.js frontend
cd frontend
npm install
npm run dev
```

### Access

| Service            | URL                                        |
| ------------------ | ------------------------------------------ |
| Frontend (Vue.js)  | http://localhost:5173                      |
| Symfony API        | http://localhost:8080                      |
| FastAPI Router     | http://localhost:8000                      |
| FastAPI Swagger UI | http://localhost:8000/docs                 |
| Symfony API Docs   | http://localhost:8080/api/doc.json/default |

---

## 📁 Project Structure

```
polymind/
├── .github/
│   ├── workflows/
│   │   ├── fastapi.yml     # FastAPI CI (Pytest)
│   │   ├── symfony.yml     # Symfony CI (PHPUnit)
│   │   └── vue.yml         # Vue.js CI (Build)
│   └── ISSUE_TEMPLATE/
├── docs/
│   └── uml/
│       ├── sequence.puml
│       ├── use-case.puml
│       ├── class-diagram.puml
│       └── architecture.md
├── symfony-app/            # PHP 8.3 / Symfony 7.4 backend
├── fastapi-router/         # Python 3.11 / FastAPI AI router
├── frontend/               # Vue.js 3 chat interface
├── docker-compose.yml
└── README.md
```

---

## 🧪 Running Tests

```bash
# FastAPI (Pytest)
cd fastapi-router
pytest tests/ -v

# Symfony (PHPUnit)
cd symfony-app
php bin/phpunit --testdox
```

---

## 📄 Documentation

-   [Architecture Diagram](./docs/uml/architecture.md)
-   [UML Diagrams](./docs/uml/)
-   [FastAPI Swagger UI](http://localhost:8000/docs)
-   [Symfony OpenAPI Schema](http://localhost:8080/api/doc.json/default)

---

## 🤝 Contributing

1. Fork the project
2. Create your branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'feat: add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

---

## 📝 License

This project is licensed under the MIT License — see the [LICENSE](LICENSE) file for details.

---

<div align="center">
Built with ❤️ by <a href="https://github.com/salim-atalla">Salim ATALLA</a>
</div>
