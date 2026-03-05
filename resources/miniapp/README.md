# IshTop Mini App

Telegram Mini App frontend for IshTop - recruitment platform.

## Tech Stack

- Vue 3 (Composition API)
- Vite
- Tailwind CSS
- Pinia (State Management)
- Vue Router
- Telegram WebApp SDK
- Axios

## Setup

1. Install dependencies:
```bash
npm install
```

2. Create `.env` file:
```bash
cp .env.example .env
```

3. Update `.env` with your API URL:
```
VITE_API_URL=http://localhost:8000/api
```

## Development

Run development server:
```bash
npm run dev
```

The app will be available at `http://localhost:3000`

## Testing with Telegram

1. Create a Telegram bot using @BotFather
2. Create a Mini App using @BotFather `/newapp` command
3. Set the Mini App URL to your dev server URL (use ngrok for HTTPS)
4. Open the Mini App in Telegram

## Build

Build for production:
```bash
npm run build
```

Output will be in `../../public/miniapp/`

## Project Structure

```
src/
├── components/       # Reusable components
│   ├── BottomNav.vue
│   ├── BannerSlot.vue
│   ├── VacancyCard.vue
│   └── LoadingSpinner.vue
├── composables/      # Composition API hooks
│   ├── useTelegram.js
│   ├── useApi.js
│   └── useGeolocation.js
├── stores/           # Pinia stores
│   ├── auth.js
│   ├── vacancy.js
│   ├── profile.js
│   └── questionnaire.js
├── views/            # Page components
│   ├── HomeView.vue
│   ├── SearchView.vue
│   ├── VacancyDetailView.vue
│   ├── ApplicationsView.vue
│   ├── ProfileView.vue
│   └── QuestionnaireView.vue
├── router/           # Vue Router config
│   └── index.js
├── utils/            # Utilities
│   └── api.js
├── App.vue           # Root component
├── main.js           # App entry point
└── style.css         # Global styles
```

## Features

- ✅ Telegram WebApp integration
- ✅ Authentication with Telegram initData
- ✅ Vacancy search with filters
- ✅ Geolocation-based nearby search
- ✅ Vacancy details and application
- ✅ Questionnaire system
- ✅ Application tracking
- ✅ User profile management
- ✅ Banner advertising system
- ✅ Saved items
- ✅ Bottom navigation
- ✅ Telegram theme integration

## API Integration

All API calls go through the `api.js` utility which:
- Automatically adds authentication token
- Adds Telegram initData header
- Handles 401 redirects
- Provides axios instance with interceptors

## State Management

Uses Pinia stores for:
- **auth**: User authentication and profile
- **vacancy**: Vacancy listings and filtering
- **profile**: Worker/Employer profile data
- **questionnaire**: Questionnaire flow and answers

## Telegram WebApp Features Used

- `initData` - User authentication
- `MainButton` - Primary action button
- `BackButton` - Navigation
- `HapticFeedback` - Touch feedback
- `themeParams` - Theme colors
- `showAlert`, `showConfirm`, `showPopup` - Dialogs
- `close()` - Close mini app
- `expand()` - Expand to full height
- `openLink()` - Open external links
- `shareUrl()` - Share functionality

## License

Proprietary - IshTop 2026
