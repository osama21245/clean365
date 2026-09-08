# Clean365 Chatbot module

Customer AI assistant: **advise packages**, **show remaining visits**, **rebook next visit**.

Does not create first-purchase subscriptions. Human chat stays in `ChattingModule`.

- API: `/api/v1/customer/chatbot/*`
- Docs: `docs/chatbot-api.md`, `docs/CUSTOMER_PACKAGES_AND_CHATBOT.md`
- Sync catalog: `php artisan pinecone:sync-packages`
