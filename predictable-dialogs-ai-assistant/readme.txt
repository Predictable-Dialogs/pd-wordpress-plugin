=== Predictable Dialogs AI Assistant ===
Contributors: jaikant@gmail.com jai@predictabledialogs.com 
Donate link: https://predictabledialogs.com/
Tags: chatbot, ai chatbot, customer support, documentation, rag chatbot
Requires at least: 6.0
Tested up to: 6.9
Stable tag: 0.1.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add an AI chatbot to your WordPress site that answers visitor questions using your knowledge base.

== Description ==

Predictable Dialogs AI Assistant connects your WordPress site to **Predictable Dialogs**, a hosted AI chatbot service that lets you train a bot on your documentation/FAQs and embed it on your website.

Once connected, the plugin displays your Predictable Dialogs web widget so visitors can ask questions and get answers from the content you’ve configured on Predictable Dialogs.

### Key features

* Add an AI chatbot widget to your site
* Answers questions based on the documentation/knowledge sources you configure in Predictable Dialogs
* Optional session association with logged-in WordPress users (see “Data & Privacy”)

### Predictable Dialogs account required

This plugin requires an active Predictable Dialogs account because the chatbot runs as a hosted service on Predictable Dialogs infrastructure. Predictable Dialogs is designed to help you deploy AI-powered web chat interfaces without building the entire stack yourself.

Get started:
* Website: https://predictabledialogs.com
* Docs: https://predictabledialogs.com/docs
* Plugin source code: https://github.com/Predictable-Dialogs/pd-wordpress-plugin
* Widget source code: https://github.com/Predictable-Dialogs/agent-embed

### External service disclosure (WordPress.org “Service” requirement)

This plugin connects to and embeds content from the following third-party service:

**Service name:** Predictable Dialogs  
**Service website:** https://predictabledialogs.com
**Service docs:** https://predictabledialogs.com/docs
**Terms:** https://predictabledialogs.com/terms
**Privacy policy:** https://predictabledialogs.com/privacy

What is transmitted to the service?
* The chatbot widget loads from Predictable Dialogs to render the chat experience on your site.
* Visitor chat messages and the bot’s responses are processed by Predictable Dialogs (and stored as conversation “sessions” in your Predictable Dialogs dashboard).
* If you enable the optional “save logged-in user information” setting in this plugin, the plugin will send logged-in WordPress user details along with the chat session so you can view them in the “Sessions” area of the Predictable Dialogs app.


### Branding link

The chat widget may include a “Predictable Dialogs” branding link. Predictable Dialogs offers plans that allow white-labeling/branding removal.

== Installation ==

1. Install the plugin from the WordPress plugin directory, or upload the plugin zip file.
2. Activate the plugin through the “Plugins” menu in WordPress.
3. In WordPress, open **Predictable Dialogs** from the left admin menu.
4. Sign up / sign in at Predictable Dialogs and create an agent (chatbot) and configure it with your documentation/knowledge sources.
5. Copy the required embed code details from your Predictable Dialogs dashboard and paste them into the plugin settings.
6. Save settings and confirm the widget appears on your site.

== Frequently Asked Questions ==

= Do I need a Predictable Dialogs account? =
Yes. This plugin embeds a chatbot that is hosted and served by Predictable Dialogs, so an account is required.

= What data does the plugin collect? =
By default, the plugin itself does not track WordPress users. The chatbot conversation is handled by Predictable Dialogs as part of providing the service. If you enable the optional checkbox to associate logged-in WordPress users with sessions, logged-in user details will be sent to Predictable Dialogs and attached to that chat session.

= Can I remove the “Predictable Dialogs” branding link? =
Predictable Dialogs offers white-labeling / branding removal on paid plans.

= Why isn’t the chatbot answering correctly? =
Most accuracy issues are related to the content connected to your agent (documentation/FAQs). Review your agent’s knowledge sources and settings in the Predictable Dialogs dashboard and docs.

== Screenshots ==

1. Plugin settings page (connect your Predictable Dialogs agent)
2. Chatbot widget on the front-end of a WordPress site
3. Example conversation and session view in Predictable Dialogs (optional)

== Changelog ==

= 0.1.0 =
* Initial release: Connect and display a Predictable Dialogs AI chatbot widget on WordPress.

== Upgrade Notice ==

= 0.1.0 =
Initial release.
