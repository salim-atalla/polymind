from fastapi import FastAPI
from app.routers import chat
from app.core.config import settings

app = FastAPI(
    title="PolyMind Router",
    description="Intelligent AI routing microservice",
    version="1.0.0",
)

app.include_router(chat.router, prefix="/api", tags=["chat"])

@app.get("/health")
def health_check():
    return {"status": "ok", "env": settings.app_env}