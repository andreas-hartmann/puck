# Puck — A WordPress plugin for automating news feeds and mailing lists

Puck is a WordPress plugin to generate news feeds from a webhook as well as notify subscribed users via email. It supports any number of feeds and subscribers and makes opting in and out of notifications easy for the user.

## Features

- **Multiple Webhooks**: Create and manage multiple feeds with different webhooks and API keys. Trigger the webhook with the content of a news item to generate an article.
- **Shortcode for subscribing**: Use the included shortcode to display a sign up form for users to subscribe to your feed.
- **Email Notifications**: Notify subscribers via email when a news item is created.
- **Unsubscribe Links**: Subscribers can opt out of further notifications with the included opt out link.

## Project status

This is currently an MVP - it does the minimum of what I wanted it to do for my use case, but it's rough around the edges.

## Installation

1. **Clone the Repository**:
    ```sh
    git clone https://github.com/andreas-hartmann/puck.git
    ```
2. **Upload to WordPress**:
    Upload the `puck` directory to the `/wp-content/plugins/` directory of your WordPress installation.

3. **Activate the Plugin**:
    Navigate to the WordPress admin panel, go to Plugins, and activate the Puck plugin.

## Usage

### Admin Settings
Navigate to **Settings > Webhook Consumer** to manage your webhooks:

- **Add a Webhook**: Click "Add Webhook", enter a name, and generate an API key. Don't forget to save.
- **Remove a Webhook**: Click the "Remove" button next to a webhook to delete it.

### Shortcodes
Use the following shortcode to create a subscription form for a specific webhook:
```shortcode
[puck_subscribe webhook="example_webhook"]
```
Replace `example_webhook` with the name of your webhook.

### Webhook Call
To trigger a webhook, use the following example cURL command:
```sh
curl -X POST https://your-site.com/wp-json/webhook/v1/receive/your_webhook_name/ \
-H "Content-Type: application/json" \
-d '{
  "title": "Test Post",
  "content": "This is the content of the post.",
  "api_key": "your_api_key"
}'
```
Replace `your_site.com` with your website URL, `your_webhook_name` with the webhook name, and `your_api_key` with the corresponding API key.
USE HTTPS! The API key is secret and could be abused if leaked.
