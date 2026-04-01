# PolyMind — Architecture

## System Overview
```
┌─────────────────────────────────────────────────────────────────┐
│                         PolyMind                                │
│                                                                 │
│  ┌─────────────┐    ┌──────────────┐    ┌───────────────────┐  │
│  │   Vue.js 3  │    │ Symfony 7.4  │    │  FastAPI (Python) │  │
│  │  Frontend   │───▶│   Backend    │───▶│   AI Router       │  │
│  │   :5173     │    │    :8080     │    │     :8000         │  │
│  └─────────────┘    └──────┬───────┘    └────────┬──────────┘  │
│                            │                     │             │
│                     ┌──────▼───────┐    ┌────────▼──────────┐  │
│                     │ PostgreSQL   │    │   AI Providers    │  │
│                     │    :5432     │    │  OpenAI / Claude  │  │
│                     │             │    │     / Gemini       │  │
│                     └─────────────┘    └───────────────────┘  │
└─────────────────────────────────────────────────────────────────┘
```

## Services

| Service | Technology | Port | Role |
|---------|-----------|------|------|
| Frontend | Vue.js 3 + Vite | 5173 | Chat UI |
| Backend | Symfony 7.4 + PHP 8.3 | 8080 | API Gateway + Auth |
| Router | FastAPI + Python 3.11 | 8000 | AI Routing Engine |
| Database | PostgreSQL 15 | 5432 | Data persistence |

## Routing Logic

| Intent | Primary Model | Fallback 1 | Fallback 2 |
|--------|-------------|-----------|-----------|
| 💻 Code | Claude (Anthropic) | OpenAI | Gemini |
| ✍️ Text | GPT-4o (OpenAI) | Claude | Gemini |
| 🎨 Image | Gemini (Google) | OpenAI | Claude |
| ❓ General | GPT-4o (OpenAI) | Claude | Gemini |

## Data Flow

1. User sends prompt via Vue.js
2. Symfony validates JWT token
3. Symfony forwards to FastAPI
4. FastAPI analyzes intent (LLM call)
5. FastAPI routes to best AI model
6. AI response returned to Symfony
7. Symfony saves conversation to PostgreSQL
8. Response returned to Vue.js with AI model badge

## CI/CD Pipelines

| Pipeline | Trigger | Steps |
|----------|---------|-------|
| FastAPI CI | Push to main | Install → Pytest |
| Symfony CI | Push to main | Install → Migrate → PHPUnit |
| Vue.js CI | Push to main | Install → Build |
