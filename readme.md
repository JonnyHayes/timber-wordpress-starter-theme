# Base theme

##Getting started with this theme:
1. Create a new repository on GitHub, BitBucket or whatever version control repository you prefer.
1. Clone this repository using `git clone git@github.com:JonnyHayes/timber-wordpress-starter-theme.git`
1. Rename the project folder `mv mvnp-starter-theme ./project-name`
1. Navigate into the project folder `cd project-name` and delete the .git folder `rm -rf .git`
1. Initialize a new local git repo and add your remote repo as the origin `git init && git remote add origin ssh://{your-repo-url}`
1. *Update the package.json with your project's details.* This is important for WP theme meta, and to keep it distinct from this project.
1. From the project folder, run `npm install && gulp default`.
1. Add all the files `git add .` and make the first commit `git commit -a && git push -u origin master`
1. Begin watching files for updates `gulp watch &`

\* **Note:** This project now uses gulp 4.0.0. If you are getting errors while running gulp, you may need to delete and reinstall your local version @^4.0.0. For me, I needed to run `rm /usr/local/bin/gulp`, then `npm i -g gulp@4.0.0`, and finally, `ln -s /usr/local/Cellar/node/10.5.0_1/bin/gulp /usr/local/bin`. For whatever reason, npm wasn't installing/linking the newest version of gulp in usr/local/bin.

##Features
###Templates, styles and scripts
The theme has main .js and .css files, but it will also load scripts and styles specific to each page template as long as they are named properly and placed in their corresponding views directory. To create new page templates and associated files, run either `npm run build-templates {template-name}` from anywhere in the project or `bin/build-templates.sh {template-name}` from the project root. This will create .php, .twig, .scss, and .js files in the appropriate directories. This will help keep page-specific scripts and styles from interfering with other pages.

###SEO
Three custom fields have been added to pages, posts and events for title, description and image. These are basically overrides for the default values for SEO. These also include Facebook and Twitter snippets. Since they are overrides they are optional.

The theme creates an XML sitemap in the Wordpress root directory when posts are updated. Only post types in the `$sitemap_posts` array, found in functions.php, are added to the sitemap. This will keep unwanted posts (like galleries) from being indexed. Just add the name of any new post types to this array to map them. The meta information is created on a per-page basis, pulling info from the post title, description and thumbnail.

###Mobile Detection
Server-side mobile detection is included. This script can detect device type, device manufacturer, browser and many other things. To use the class:
```
$mobile = new Mobile_Detect();

$mobile->isMobile();
$mobile->is(Chrome);
...
```
For full documentation see http://mobiledetect.net/

###Wordpress Customization
There are several WP customizations to note.

- **Custom images sizes.** Wordpress will create new images based on the sizes found in the `mvnp_basic_custom_image_sizes` function. Just give it a height, width and a crop origin, and WP will do the rest. Note that you will need to regenerate thumbnails for existing images to get the new sizes. [This](https://wordpress.org/plugins/regenerate-thumbnails/) plugin does that very nicely. This *should* only need to be done in development. In production, there will be no existing images that need to be resized.

- **Custom nav locations.** Adding locations to the `make_menus` function will add menu locations to the admin's menu section. Then add those locations to the timber context in the `add_to_context` function. Widget locations follows a similar process.

- **Sidebar.** The sidebar is on by default and displays the default widget location. To disable the sidebar, comment out the sidebar line in the `add_to_context` function. To have the sidebar show only the content in the sidebar.twig file, change the `$data['sidebar']` value to true. Note that the sidebar in the functions file s global. If you want the sidebar to only appear on certain pages, disable it in functions and add it to the context in those pages' php template.

###Media queries, Bootstrap and collation
While this theme does not use Bootstrap, it does use Bootstrap collation. This means you can use `.row` and `.col-{infix}-{span}` as you would in any Bootstrap project. The collation has been modified in several ways as well. First of all, all col-\* elements are `display: inline-block;` This allows last-row column elements inside elements set to `text-align: center;` to center. If you want col-\* elements to float, simply add 'clearfix' class to your row.

Next, you, as the developer, have full control over the name, amount and size of all the column breakpoints, the number of columns and gutter size. By default, in the \_variables.scss file you will see several variables with pixel values. These are used as breakpoint values for columns and media queries. Further down the file you will see the `$grid-breakpoints` variable. This is what all column breakpoints are built from. If you need to add additional breakpoints or change the infix, simply add or change the values in this variable. To maximize consistency, use variables for the sizes. This allows you to use those variables in the media queries that you write yourself. As long as your MQs use those, everything will break at the same sizes universally. It is important to note that the media queries for columns now use 'break down' or 'max-width' methodology, meaning smaller sizes overwrite larger ones. The media queries you write should follow this format as well. Example:

```
.element{
	width: 500px;

		@media(max-width: $tablet){
			width: 200px;
		}

		@media(max-width: $phone){
			width: 100px;
		}
}
```

###Image Compression
Two binaries are included, pngquant and jpeg-recompress, which will automatically compress files uploaded through the Wordpress media manager. Three versions of each are included. One each for Mac OS, Linux and Windows. Running the default gulp task will duplicate the appropriate binaries and rename them (without the prefix). The hook for the compression can be found in functions.php and the function name is `resample_image`. Details and options for each command can be found at their respective websites:

https://pngquant.org/

https://github.com/danielgtaylor/jpeg-archive

This however does have one big, ugly dependency: [Imagick](https://www.imagemagick.org/script/index.php). While neither pngquant or jpeg-recompress require Imagick to work, jpeg-recompress cannot operate on images saved in CMYK colorspace. It just craps out. So to insure that the website doesn't crash when trying to upload images, we need to use Imagick to convert the .jpg files to sRGB. Installing Imagick can be a gigantic pain in the ass depending on your OS. Linux and Mac OS is by far the easiest with just a few terminal commands. Windows… don't get me started. I was eventually able to get Imagick installed on Windows, but it never worked on .jpg files.

Here are some helpful tips for installation:

######Linux (Debian)
`sudo apt install php-imagick`.

If you are running lampp, your web service won't have access, so you will need to install it using `pecl`. Details here (P.S. It's still super easy)

https://lampjs.wordpress.com/2015/02/25/installing-image-magick-imagick-extension-for-php-in-xampp-for-linux-arch-linux/

Here are some links for other distros as well

Red Hat - https://www.cyberciti.biz/faq/howto-install-imagemagick-rpm/

Fedora - https://jhalog.wordpress.com/2010/11/09/install-imagemagick-in-fedora-with-yum/

Gentoo - https://packages.gentoo.org/packages/media-gfx/imagemagick

Arch - https://aur.archlinux.org/packages/php-imagick/

######Mac OS
Can apparently be installed with brew, but like Debian above, you will need to install it with `pecl` in your MAMP/XAMP/AMPPS/etc. php folder. For MAMP, that is located in `{MAMP-dir}/bin/php/{php-version}/bin`

######Windows
Hoo boy. First install the application from imagemagick.org. Then you will need .dll files which can be found here

http://windows.php.net/downloads/pecl/releases/imagick/

The tricky part is making sure the versions are compatible. After you've moved the imagick.dll to your php extension folder, and included it in your php.ini file, you will need to copy (or link) the CORE_RL_wand_.dll, CORE_RL_magick_.dll files to the application folder. Once thats done, navigate to the folder containing php for your web service and run `php.exe -m`. If it says imagick is included, you should be golden. Otherwise grab some new binaries and libraries and try again. Jeez.

---
Regardless of which OS you are using, you will need to include the Imagick extension in your php.ini file. If you are unsure of where your php.ini file is, I have found the easiest way to locate it is to run `phpinfo()` in your functions.php file. Running it this way vs. from the command line will ensure you are getting the file that your web service is using. Once found, on Mac and Linux, run the following command:

`echo "extension=imagick.so" >> path/to/your/php.ini`

I'm not a Windows guy, so I don't know an equivalent command, so I just manually added the following line to the file:

`extension=php_imagick.dll`

Just to note, I've noticed that you will need at least version 6.9.6-6 of imagemagick (7.0.6-0 is the current at the time of writing this) or else Wordpress will really mess up CMYK .jpeg files when it resizes. It is also recommended that you install with LCMS for the color profiles to work.

###Front end signup and login
Three functions are included that will handle Wordpress user auth: `doLogin`, `doLogout`, and `doSignup`. All three make ajax posts to admin-ajax.php, extended by helper functions in php/auth.php. All three also take two parameters, `data` and `_callback`. The signup function creates a new user with no role. These new users can't really do anything or even access the wp-admin.

All users, whether they signup on the site or are created through the admin, will get their own "user page." This is really just a custom post type that is hidden from the admin and can only be accessed by the owner or an administrator. User pages are located at `/users/{username}`.

To disable *all* auth functionality, comment out, or change the value of the `can_log_in` property to false in the `add_to_context` function in functions.php.

###Events and Google Calendar
An events calendar is built in. At the moment it has not been tested without google calendar being available to it. To get google calendar running, you must allow access through the google calendar developer console, create a secret file, and authorize the project to access. When you have created a secret key and added it to the project (default path is `/php/vendor/client_secret.json`), you must change the Wordpress paths in `php/google-calendar.php` to absolute paths, uncomment the `getClient()` function call at the bottom, and run the file with `php php/google-calendar.php`. Your console will provide you with a web link. Open that link, authorize the account and copy the code. Paste that code back into your console and hit return. The project has now been authorized and a credentials file has been created. Revert the `php/google-calendar.php` to its original state, and the calendar is ready to use.

On the events list page in the wp-admin, there is a button that will import all future events from the linked google account. If for whatever reason you need to get past events, change the date in the import function in `php/admin.php`. All saved events and any new events will be automatically pushed to the calendar.

###Galleries
To access galleries add `$context['gallery'] = Timber::get_post({gallery-id});` to your .php template file and add the following to your .twig template.
```
<div class="gallery-wrapper">
	{% for image in gallery.gallery_images %}
		<img src="{{TimberImage(image.props).src('your-image-size')}}">
	{% endfor %}
</div>
```
You can access the fields with `image.props`, `image.link`, `image.alt`, `image.name`, `image.copy` and `image.iframe`. The gallery admin has no options for sizing, effects or anything else for that matter. All of those options are handled through bxSlider, which is included in the main JS file. To create a slider or gallery on your list of images, use `$('.gallery-wrapper').bxSlider({options})`. Full documentation on options can be found [here](http://bxslider.com/options).

###Calendars
A simple calendar function called `calendar` attaches calendars to elements in your code. To invoke use something like this:
```
date = calendar({
	element: calendar.create(),
	on_select: function(new_date){
		{Do stuff when a date is selected}
	},
	is_valid: function(date){
		{Logic to check whether the calendar date is selectable}
	},
});
...
$('#myCalendarElement').append(date.element);
```
The basic calendar structure is contained in the `calendar.create()` method and can be modified in functions.js. Bu default calendars are attached to every input of type "date" and appear on focus. When a date is selected, it is sent to the value of the input. Calendars can be attached to static elements as well and always be visible

###I18n & L10n
I18n is built in via gulp. All app/\*.js, all \*.php and all views/\*.twig will trigger the gulp internationalization script to generate a .pot file in the languages directory. As you code, be sure to wrap your static text in i18n translation functions. These look like `__('Text to translate', 'text_domain')` and `_e('Text to translate', 'text_domain')`. The `_e` function will 'echo' the translation and should be used primarily on the front end, while the `__` should be used for passing or assigning translatable strings. Only the `__` function will work in Javascript at the moment. It is recommended that you keep the text domain as `mvnp_basic` because you will have to *change every text domain throughout the project* otherwise. Here is a simple -pie script to find and replace the text domain (run from the root of the project):

`find . -not -path './node_modules/*' -not -path './php/vendor/*' -not -path './.git/*' -not -path './plugins/*' -not -path './bin/*' -not -path './images/*' ! -iname ".*" -type f | xargs perl -pi -e 's/mvnp_basic/{your_new_name}/g'`

I'm still going to recommend sticking with the original text domain, but if you are brave enough to run this, be my guest. I believe you will also have to restart gulp afterwards.

If and when you decide that you need l10n, install qTranslate X (https://qtranslatexteam.wordpress.com/), activate new languages, and the rest should be automatic (aside from entering the translations themselves, of course). I tried to include qTX in the theme, but it was being uncooperative.

To generate .po and .mo files, I strongly recommend POEdit (https://poedit.net/). It costs about $20, but it will make your life so much easier. It can even handle .twig files.

###Modals
Image and ajax modals are built in with no additional coding needed. For images, simply add the `modal-img` class. This will open a modal with that image at that size. If you want to open a different image (larger for instance), add `data-target="{image-url}"`. Ajax works in a similar way, but on anchor tags. The anchor's href is used as the source for the ajax call, and the `data-target` is the target element that you want to show in the modal. If no target is provided, or the target is invalid, `[role="main"]` is used. To invoke a modal in javascript use the function `makeModals({cb})`

###Notifications
Front end notification badges are invoked with `notification({message}, {level})`. There are three levels: 'success', 'error' and 'normal'. All levels that are not success or error are treated as normal, as are messages with no level. There is no limit to the number of concurrent messages and all badges will disappear after a set amount of time, which can be configured in functions.js.

###Parallax
A simple parallax script is included, just give your element the `.parallax` class. All it does is move the `background-position` of the element based on its position within the viewport. Currently, the rate that the background scrolls is based on a ratio of the image's height and width. One could write `data-` attribute detection into the script to store user defined rates, if one were so inclined. It clamps the value between 0% and 100% so it will never go beyond the boundaries of the element.

###Google maps
Google maps are included ~~, but off by default. To turn them on, uncomment the script registration in the mvnp_basic_load_scripts function~~. Two helper JS functions are included: `initMap({element}, {name}, {coordinates})` and `codeAddress({element}, {name}, {address}, {makeMap})`. initMap will instantiate a map on an element of your choice, and takes the element, marker name, and location lat/lng coordinates as parameters. This is useful for hard coded map locations. If you need to make maps dynamically, based on an address string, use codeAddress. It takes the element, marker name, and address string as parameters and if the makeMap argument is set to true, will use initMap to create the map. Note that the API key used in the script will only work on domains registered with the API key, which means that they might not display on your development environment.

###Custom fields
Advanced Custom Fields is included in the theme, and will behave as expected. However, moving forward we can begin to write our custom fields directly into the php. This will prevent discrepancies between fields in our local environments and dev/stage/prod and will prevent non-developers from inadvertently breaking the fields.

Writing fields is fairly simple, but requires a lot of parameters. Therefore, I have included the `acf-fileds.demo.php` file in the php directory. It has a sample fieldset with a couple sample fields, including a repeater. This file also includes documentation on each setting provided by the ACF website. Additionally, when you create a new page template via the shell script detailed above, a new .php file will be created and included for the express purpose of writing ACFs for that template. This will only be loaded when a page using that template is loaded. This is done to cut down on the size of php files and to make things more clear in general. Of course for ACFs that are not template-specific, they will need to be written into the global ACF include, `acf-fileds.php`.

You can always forgo this whole process and enter your fields through the wp-admin as usual, just try not to do both. It will be confusing.

Full vendor documentation about writing fields found here:
https://www.advancedcustomfields.com/resources/register-fields-via-php/

###"Standard Pages"
There are some pages that are so ubiquitous that nearly every website uses them. In such cases within this theme, you can designate those pages within the admin. Currently "about" and "contact" are supported and you can designate these in Settings > Reading. Once you have chosen a page to represent these archetypes, you will no longer be able to choose a template for them (same with front page, but that was built into Wordpress already) and they may have new special fields associated with them. In the future I may add support for an FAQ page and a Privacy Policy page since these too are widely used.

##Detailed code documentation
[JavaScripts Documents](https://github.com/JonnyHayes/timber-wordpress-starter-theme/wiki/jsdoc.md)
