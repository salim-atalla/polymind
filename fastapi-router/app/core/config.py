from pydantic_settings import BaseSettings

class Settings(BaseSettings):
    openai_api_key: str
    anthropic_api_key: str
    gemini_api_key: str
    app_env: str = "development"
    log_level: str = "INFO"

    class Config:
        env_file = ".env"

settings = Settings()