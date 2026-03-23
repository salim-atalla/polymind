<div align="center">

# 🧠 PolyMind

### One chat interface. Multiple AI brains. Always the right one.

![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php)
![Symfony](https://img.shields.io/badge/Symfony-6-000000?style=for-the-badge&logo=symfony)
![Python](https://img.shields.io/badge/Python-3.11-3776AB?style=for-the-badge&logo=python)
![FastAPI](https://img.shields.io/badge/FastAPI-0.100-009688?style=for-the-badge&logo=fastapi)
![Vue.js](https://img.shields.io/badge/Vue.js-3-4FC08D?style=for-the-badge&logo=vue.js)
![Docker](https://img.shields.io/badge/Docker-Compose-2496ED?style=for-the-badge&logo=docker)
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

---

## 🏗️ Architecture

```
[Vue.js] → [Symfony API] → [FastAPI Router] → [OpenAI / Claude / Gemini]
```

-   **Vue.js** — Chat user interface
-   **Symfony 6** — Main backend, authentication, chat history
-   **FastAPI** — AI router microservice, prompt analysis, model selection
-   **PostgreSQL** — Users, conversations, messages storage
-   **Docker** — Full containerized environment

---

## 🚀 Getting Started

### Prerequisites

-   Docker & Docker Compose
-   Git
-   API keys: OpenAI, Anthropic, Google Gemini

### Installation

```bash
# 1. Clone the repository
git clone https://github.com/salim-atalla/polymind.git
cd polymind

# 2. Copy environment file
cp .env.example .env

# 3. Add your API keys in .env
OPENAI_API_KEY=your_key_here
ANTHROPIC_API_KEY=your_key_here
GEMINI_API_KEY=your_key_here

# 4. Start the application
docker-compose up -d

# 5. Run migrations
docker-compose exec symfony-app php bin/console doctrine:migrations:migrate
```

### Access

| Service            | URL                        |
| ------------------ | -------------------------- |
| Frontend (Vue.js)  | http://localhost:5173      |
| Symfony API        | http://localhost:8080      |
| FastAPI Router     | http://localhost:8000      |
| API Docs (Swagger) | http://localhost:8000/docs |

---

## 📁 Project Structure

```
polymind/
├── .github/
│   ├── workflows/          # CI/CD pipelines
│   └── ISSUE_TEMPLATE/     # Bug report, Feature request
├── docs/
│   ├── uml/                # UML diagrams
│   └── api/                # API documentation
├── symfony-app/            # PHP Symfony backend
├── fastapi-router/         # Python AI router
├── frontend/               # Vue.js chat interface
├── docker-compose.yml
└── README.md
```

---

## 🧪 Running Tests

```bash
# Symfony (PHPUnit)
docker-compose exec symfony-app php bin/phpunit

# FastAPI (Pytest)
docker-compose exec fastapi-router pytest
```

---

## 📄 Documentation

-   [Architecture & UML Diagrams](./docs/uml/)
-   [API Documentation](http://localhost:8000/docs)

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
