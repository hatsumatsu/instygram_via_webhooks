Instygram via Webhooks
======================

This plugin receives Instagram posts via IFTTT webhooks.

## Description

Use this plugin in conjunction with IFTTT to insert Instagrams into either your posts or a custom Instagram post type.

Unlike IFTTT’s Wordpress recipe, this will actually upload images to your server, which will give you greater flexibility in theme-making and, depending on your setup, possibly better performance. You will also get all the usual Wordpress thumbnails and a “featured image” thumbnail.

This plugin requires the Wordpress REST API v2. 

Thanks to Hugh Lashbrooke for his very cool Wordpress plugin template. https://github.com/hlashbrooke/WordPress-Plugin-Template

### Installation

1. Log in to your account with IFTTT.com
2. Add the IFTTT recipe: https://ifttt.com/recipes/397411-instagrams-in-wordpress-via-webhooks
3. IMPORTANT: change "yourserver.com" in the recipe to reflect the domain of your Wordpress install.
4. Back on your copy of Wordpress, install the Wordpress REST API v2. https://wordpress.org/plugins/rest-api/ (note: this will be included in the Wordpress core very soon.)
5. Copy this plugin to your wp-plugins folder and Activate.

### Optional Settings

By default, Instagram images will upload into your normal Wordpress “posts”. In my experience, having a custom post type is much more useful — in fact, that’s the whole reason I built this thing. So check Custom Posts “on” to get that loveliness.

If you are in a multi-user environment, you can select which user the posts are accredited to. By default it’ll be #1.

You can use IFTTT to trigger posts via some kind of hashtag or criteria other than “my pictures”. It would be a pretty good idea to review those for content before publishing live on your blog, so you can set New Post Status to “Draft” or “Pending”.

### Coming Soon

- Instagrams that insert into normal posts should have the image in the content in an img tag. As this is not my normal use case, I haven’t got to it yet.
- Tags

### Frequently Asked Questions

> What the hell, why isn’t this working?

Do you have the IFTTT Maker recipe installed? https://ifttt.com/recipes/397411-instagrams-in-wordpress-via-webhooks

Did you change "yourserver.com" in the IFTTT recipe to reflect your own server?

> How does this validate that incoming JSONs are in fact from IFTTT?

It doesn’t. If this is necessary, desirable, or necessary, please chime in on the Github Issues.