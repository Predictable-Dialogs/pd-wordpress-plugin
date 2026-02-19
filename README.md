

# AI Chatbot Plugin For WordPress by Predictable Dialogs

## Generate plugin zip

```bash
zip -r pd-ai-chatbot-wordpress.zip pd-ai-chatbot -x '*.DS_Store' '*__MACOSX*'
```

## Install in WordPress

1. WordPress Admin -> `Plugins` -> `Add New` -> `Upload Plugin`
2. Upload `pd-ai-chatbot-wordpress.zip`
3. Activate plugin
4. Open `Predictable Dialogs` from the left admin menu

## Standard widget

1. In Predictable Dialogs install page, choose `WordPress` + `Standard`
2. Copy shortcode, for example:

```text
[pd pd="my-agent-name" width="100%" height="600px"]
```

3. Paste shortcode into a WordPress page or post
4. Open the page and verify widget loads and dimensions are respected

## Popup widget

1. In install page, choose `WordPress` + `Popup`
2. Configure popup settings:
   - Auto show: on/off
   - Delay seconds
3. Copy generated snippet (for example `Agent.initPopup({...})`)
4. In WordPress plugin settings:
   - Paste snippet into initialization snippet field
5. Open site frontend and verify popup appears as configured

## Bubble widget

1. In install page, choose `WordPress` + `Bubble`
2. Configure bubble settings:
   - Preview message toggle/text
   - Placement left/right
   - Button size
   - Button color
   - Custom icon URL
3. Copy generated `Agent.initBubble({...})` snippet
4. Paste snippet into WordPress plugin settings
5. Verify bubble UI and behavior match configuration

## Test excluded pages patterns

In plugin settings `Excluded pages`, validate these patterns:

- `/app/*`
- `/app`
- `/app?param=1`
- `/app?param=*`
- `/app/*?param=*`

Verify widget is hidden on matching routes and visible on non-matching routes.

## User mapping checks

1. Verify user/context mapping values are passed:
   - `user_id`
   - `user_name`
   - `user_email`
   - `user_segments`
