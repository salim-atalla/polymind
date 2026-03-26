import pytest
from unittest.mock import AsyncMock, patch
from app.services.selector import select_model_and_call
from app.models.schemas import IntentType

MOCK_RESPONSE = {
    "response": "Here is your answer.",
    "model":    "test-model",
    "provider": "test-provider",
}

@pytest.mark.asyncio
async def test_code_routes_to_claude():
    with patch("app.services.providers.claude.call_claude",
               new_callable=AsyncMock, return_value=MOCK_RESPONSE) as mock:
        result = await select_model_and_call("Write a sort function", IntentType.CODE)
        mock.assert_called_once()
        assert result["provider"] == "test-provider"

@pytest.mark.asyncio
async def test_text_routes_to_openai():
    with patch("app.services.providers.openai.call_openai",
               new_callable=AsyncMock, return_value=MOCK_RESPONSE) as mock:
        result = await select_model_and_call("Write an email", IntentType.TEXT)
        mock.assert_called_once()
        assert result["provider"] == "test-provider"

@pytest.mark.asyncio
async def test_image_routes_to_gemini():
    with patch("app.services.providers.gemini.call_gemini",
               new_callable=AsyncMock, return_value=MOCK_RESPONSE) as mock:
        result = await select_model_and_call("Generate a sunset", IntentType.IMAGE)
        mock.assert_called_once()
        assert result["provider"] == "test-provider"

@pytest.mark.asyncio
async def test_fallback_on_primary_failure():
    with patch("app.services.providers.claude.call_claude",
               new_callable=AsyncMock, side_effect=Exception("Claude down")):
        with patch("app.services.providers.openai.call_openai",
                   new_callable=AsyncMock, return_value=MOCK_RESPONSE) as fallback:
            result = await select_model_and_call("Write a function", IntentType.CODE)
            fallback.assert_called_once()
            assert result == MOCK_RESPONSE

@pytest.mark.asyncio
async def test_raises_when_all_providers_fail():
    with patch("app.services.providers.claude.call_claude",
               new_callable=AsyncMock, side_effect=Exception("down")):
        with patch("app.services.providers.openai.call_openai",
                   new_callable=AsyncMock, side_effect=Exception("down")):
            with patch("app.services.providers.gemini.call_gemini",
                       new_callable=AsyncMock, side_effect=Exception("down")):
                with pytest.raises(Exception, match="All providers failed."):
                    await select_model_and_call("anything", IntentType.CODE)