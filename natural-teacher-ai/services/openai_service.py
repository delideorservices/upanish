import os
import logging
from openai import AsyncOpenAI
from typing import AsyncGenerator
from langchain_openai import ChatOpenAI  # Compatible with langchain-openai >= 0.1.0
from langchain.schema import HumanMessage, SystemMessage

logger = logging.getLogger(__name__)

class OpenAIService:
    def __init__(self, api_key=None):
        self.api_key = api_key or os.getenv("OPENAI_API_KEY")
        if not self.api_key:
            raise ValueError("OpenAI API key is required")

        # OpenAI SDK Client for streaming
        self.client = AsyncOpenAI(api_key=self.api_key)

        # LangChain chat model
        self.chat_model = ChatOpenAI(
            openai_api_key=self.api_key,
            model="gpt-4o",  # or gpt-3.5-turbo
            temperature=0.7
        )

    def get_llm(self):
        return self.chat_model

    async def get_completion_streaming(self, prompt: str) -> AsyncGenerator[str, None]:
        try:
            response = await self.client.chat.completions.create(
                model="gpt-4o",
                messages=[
                    {"role": "system", "content": "You're a helpful teaching assistant."},
                    {"role": "user", "content": prompt}
                ],
                temperature=0.7,
                max_tokens=2000,
                stream=True
            )

            async for chunk in response:
                content = chunk.choices[0].delta.content
                if content:
                    yield content

        except Exception as e:
            logger.error(f"Error getting streaming completion: {e}")
            yield "Sorry, I had trouble generating a response."
            yield "Please try again later."

    def get_completion(self, prompt: str, system_message: str = None) -> str:
        try:
            from openai import OpenAI
            client = OpenAI(api_key=self.api_key)

            messages = []
            if system_message:
                messages.append({"role": "system", "content": system_message})
            messages.append({"role": "user", "content": prompt})

            response = client.chat.completions.create(
                model="gpt-4o",
                messages=messages,
                temperature=0.7,
                max_tokens=2000
            )

            return response.choices[0].message.content

        except Exception as e:
            logger.error(f"Error getting completion: {str(e)}")
            return "Sorry, I encountered an error generating the response."

    def format_for_age(self, content: str, age: int) -> str:
        try:
            system_message = """
            You are an educational content adapter. Your task is to rewrite 
            the provided content to make it age-appropriate and engaging 
            for the student's age while preserving all the educational value.
            """

            prompt = f"""
            Please rewrite the following educational content to be appropriate
            and engaging for a {age}-year-old student:

            {content}

            Make sure to:
            1. Use vocabulary appropriate for the age
            2. Keep sentences shorter for younger children
            3. Use engaging language and examples
            4. Maintain all educational content and accuracy
            5. Include encouraging language
            """

            return self.get_completion(prompt, system_message)
        except Exception as e:
            logger.error(f"Error formatting for age: {str(e)}")
            return content
