# PolyMind — Architecture Diagram

## System Overview

```
┌─────────────────────────────────────────────────────────────┐
│                        PolyMind                             │
│                                                             │
│  ┌─────────────┐    ┌─────────────┐    ┌─────────────────┐ │
│  │   Vue.js    │    │   Symfony   │    │    FastAPI      │ │
│  │  Frontend   │───▶│   Backend   │───▶│     Router      │ │
│  │  :5173      │    │   :8080     │    │     :8000       │ │
│  └─────────────┘    └──────┬──────┘    └────────┬────────┘ │
│                            │                    │          │
│                     ┌──────▼──────┐    ┌────────▼────────┐ │
│                     │ PostgreSQL  │    │   AI Providers  │ │
│                     │    :5432    │    │ OpenAI/Claude/  │ │
│                     └─────────────┘    │    Gemini       │ │
│                                        └─────────────────┘ │
└─────────────────────────────────────────────────────────────┘
```

## Services

| Service  | Technology            | Port | Role               |
| -------- | --------------------- | ---- | ------------------ |
| Frontend | Vue.js 3 + Vite       | 5173 | Chat UI            |
| Backend  | Symfony 6 + PHP 8.2   | 8080 | API Gateway + Auth |
| Router   | FastAPI + Python 3.11 | 8000 | AI Routing Engine  |
| Database | PostgreSQL 15         | 5432 | Data persistence   |

## Data Flow

1. User sends prompt via Vue.js
2. Symfony validates JWT and forwards to FastAPI
3. FastAPI analyzes intent and routes to best AI
4. Response saved to PostgreSQL
5. Response returned to Vue.js
