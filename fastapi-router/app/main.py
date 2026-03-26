from fastapi import FastAPI
from fastapi.openapi.utils import get_openapi
from app.routers import chat
from app.core.config import settings

app = FastAPI(
    title="PolyMind Router API",
    description="""
## 🧠 PolyMind — Intelligent AI Router

PolyMind automatically routes your prompt to the most suitable AI model based on detected intent.

### How it works
1. Send a prompt to `/api/route`
2. PolyMind analyzes the intent (code, text, image, general)
3. The best AI model is selected automatically
4. The response is returned along with metadata

### Supported Models
| Intent | Model | Provider |
|--------|-------|----------|
| 💻 Code | Claude Opus | Anthropic |
| ✍️ Text / Email | GPT-4o | OpenAI |
| 🎨 Image | Gemini 1.5 Pro | Google |
| ❓ General | GPT-4o | OpenAI |
    """,
    version="1.0.0",
    docs_url="/docs",
    redoc_url="/redoc",
    openapi_url="/openapi.json",
    contact={
        "name": "Salim Atalla",
        "url": "https://github.com/salim-atalla",
    },
    license_info={
        "name": "MIT",
        "url": "https://opensource.org/licenses/MIT",
    },
)

app.include_router(chat.router, prefix="/api", tags=["chat"])

@app.get("/health", tags=["system"], summary="Health check")
def health_check():
    """Returns the current status of the API."""
    return {"status": "ok", "env": settings.app_env}