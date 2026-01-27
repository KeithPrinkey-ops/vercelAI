# VercelAI – Laravel + Vue Streaming AI Application

This repository contains a Laravel + Vue (Vite) application demonstrating **real-time streamed AI responses** from OpenAI to the frontend. The application is designed to be simple to run locally with minimal configuration.

The **only required secret** is an OpenAI API key.

---

## Features

- Laravel backend with clean application architecture
- Vue 3 frontend powered by Vite
- Real-time streaming of OpenAI responses to the UI
- Incremental rendering of streamed chunks
- Graceful handling of error states (e.g. token exhaustion, API failures)
- Clear separation between backend logic and frontend rendering

---

## Requirements

- PHP 8.2+
- Composer
- Node.js 18+
- npm
- A valid OpenAI API key

---

## Installation

### 1. Clone the repository

```bash
git clone git@github.com:KeithPrinkey-ops/vercelAI.git
cd vercelAI
```
---
### 2. Install backend dependencies
```bash
composer install
```
---

### 3. Install frontend dependencies
```bash
npm install
---
npm install ai
```
---

### 4. Environment configuration

Copy the example environment file:

```bash
cp .env.example .env
```
---

Generate an application key:

```bash
php artisan key:generate
```

---

### 5. Add your OpenAI API key

Edit .env and add only the following value:

```bash
OPENAI_API_KEY=your_openai_api_key_here
```

---

No other secrets are required to run the application.

### 6. Run database migrations (if applicable)
```bash
php artisan migrate
```
---
### 7. Running the Application
Start the Laravel backend
```bash
php artisan serve
```
Start the Vite development server
```bash
npm run dev
```
> If you use laravel herd, it can be served the same as your other laravel apps
> The application will now be available locally, with streamed AI responses delivered directly to the frontend.

## Streaming Architecture Overview

The backend uses Laravel to initiate and manage OpenAI streaming requests

Responses are streamed incrementally rather than returned as a single payload

The frontend consumes streamed chunks in real time, enabling:

## Progressive rendering

Lower perceived latency

## Robust error handling (including OpenAI quota and token errors)

This approach mirrors production-grade AI UX patterns rather than simple request/response flows.

## Environment & Repository Notes

.env is intentionally not committed

vendor/ and node_modules/ are intentionally excluded

## Build artifacts are not committed

All required application, configuration, and tooling files are tracked in Git

A fresh clone should be runnable with only dependency installation and an OpenAI API key.

## Development & Tooling

Prettier for frontend formatting

Laravel Pint for backend formatting

ESLint for Vue and TypeScript

Vite for modern frontend builds

SSH-based Git authentication recommended

## License

This project is provided for demonstration and development purposes.  
Refer to OpenAI’s terms of service for API usage requirements.
