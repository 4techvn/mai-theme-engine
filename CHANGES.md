### Changelog

#### 1.2.0 (1/17/18)
* Added: Plugin icon when updating via Dashboard > Updates.
* Changed: Plugin name to reflect official Mai Theme brand.
* Changed: Convert sticky header from JS to CSS-only.
* Changed: Move mai_header_before and mai_header_after hooks outside of site-header.
* Changed: Mobile menu toggle now uses psuedo-elements for less markup.
* Changed: More vertical padding on text inputs.
* Changed: Allow widget entry titles to inherit font weight.
* Changed: More consistent base body background and even section background color.
* Changed: Minor tweaks to borders, spacing, etc.
* Changed: Comment edit link now doesn't alter comment layout.
* Changed: Fixed overflow breaking out of full width sections in some edge-case scenarios.
* Fixed: [grid] links adding product to cart instead of going to product page when clicking title or image.
* Fixed: Add section wrap if using a title without content.
* Fixed: Woo cross-sells and up-sells heading font size.
* Fixed: Woo qty now has a bit more right margin.
* Fixed: Sections template admin now parses [gallery] shortcodes correctly.
* Fixed: More precise handling of sub-menu widths.

#### 1.1.13.1 (1/03/18)
* Fixed: [grid] Not showing only top level posts/terms if 'parent' param was '0'.

#### 1.1.13 (1/02/18)
* Changed: Only float avatar in comments and author box.
* Changed: Safer and simpler responsive breaks for all column shortcodes.
* Changed: Hyphenate sidebar widget titles and text.
* Fixed: Horizontal scroll issue on pages with full width sections.
* Fixed: Site footer nav menu widgets not wrapping menu items on smaller screens.

#### 1.1.12 (12/28/17)
* Added: Slider max-width set in CSS so layout isn't totally broken on initial page load before Slick is initialized.
* Changed: Better docblock for template loader function.
* Fixed: Slashes being added to header and footer script metabox content when saving via Theme Settings.
* Fixed: Slider arrows were cut off by browser window on full width layout at certain browser widths.
* Fixed: Logo didn't remain centered when no header left or right content and no mobile menu assigned.
* Fixed: Woo reviews styling issue.
* Fixed: Issue with PHP 5.4 though we don't officially support PHP that low, but it was an easy fix.

#### 1.1.11 (12/21/17)
* Changed: [grid] Move 'mai_flex_entry_content' filter before more-link.
* Fixed: [grid] bg-image link not working correctly when displaying taxonomy terms.
* Fixed: Login logo not working in WP 4.9.
* Fixed: Woo qty field is now same height as button it's next to.
* Fixed: Term banner image field always shows now, since that image is used for [grid] even when banner is disabled.

#### 1.1.10 (12/20/17)
* Added: [col] 'link' param which accepts a url or a post ID to make the entire col a link.
* Changed: [col] 'image' param now accepts 'featured' to use a post's featured image when 'link' is set to a post ID.
* Fixed: [col] 'align' and 'bottom' params not working as expected.
* Fixed: Overlay and image-bg background-colors and hover colors.

#### 1.1.9 (12/19/17)
* Added: [grid] 'date_query_before' and 'date_query_after' parameters. They accept any values that strtotime() accepts. To show only posts within the last 30 days you can just use date_query_after="30 days ago".

#### 1.1.8.1 (10/22/17)
* Fixed: More full-proof genesis-settings pre update option filter.

#### 1.1.8 (10/21/17)
* Changed: Sections template no longer loads a template file, so you can use Sections template in Dashboard but still use front-page.php (or other template) in your theme.
* Changed: Entry header meta now wraps to it’s own line on smaller screens.
* Fixed: Hide empty callout divs.
* Fixed: Settings not saving correctly.

#### 1.1.7.1 (10/18/17)
* Fixed: Some custom Mai settings were not saving correctly in customizer.

#### 1.1.7 (10/14/17)
* Fixed: Some custom settings getting cleared during Genesis updates.
* Fixed: Shortcodes getting parsed by Yoast/WPSEO would break things if [grid] was used with parent="current" as a parameter.
* Fixed: Duplicate h1's, again.

#### 1.1.6.2 (10/10/17)
* Fixed: Duplicate h1's on some posts/pages under certain conditions.

#### 1.1.6.1 (9/29/17)
* Added: Extra Small and Extra Large height options to Sections template.
* Fixed: Height ratios for more consistent scaling.
* Fixed: Banner height upgrade default to 'lg' if image used.

#### 1.1.6 (9/29/17)
* Added: Banner "Height" setting in customizer.
* Added: [section] Add "Text Size" field to Sections template (and [section text_size="lg"] shortcode).
* Added: [section] Add "wrap_class" parameter to section shortcode to add a class to the wrap div.
* Added: Filter on 'mai_cpt_settings_post_types' to change which post types get Mai customizer settings support.
* Added: Single post comments link now slow scrolls to the comments section.
* Changed: Smarter handling of h1 on site-title, banner, and/or first section title, depending on what is used.
* Changed: Convert all font sizes to rem/em for 'module' or 'component' based font sizing. Uses "Major Third" sizing ratio.
* Changed: Sections template now shows breadcrumbs if they are enabled for Pages.
* Changed: Section settings now close when clicking on section content field in a Sections template.
* Changed: More thorough and more efficient filter to add custom logo.
* Changed: Bump normalize.css to 7.0.0.
* Changed: Move all media queries to mobile-first.
* Fixed: Single post footer entry meta no longer shows private taxonomies.
* Fixed: Big mobile menus getting cut off when logged in and showing toolbar.
* Fixed: [grid] Title spacing when showing image before entry.
* Fixed: Sections template now properly formats quotes to smart quotes, apostrophes, dashes, ellipses, the trademark symbol, and the multiplication symbol. Via wptexturize().
* Fixed: Admin login logo spacing when error/notice is displayed.

#### 1.1.5.1 (9/20/17)
* Fixed: Missing closing div on site header row.

#### 1.1.5 (9/15/17)
* Fixed: CPT settings from Customizer getting changed when saving CPT Archive Settings in the backend.

#### 1.1.4 (9/15/17)
* Fixed: CPT archive images would not display when using custom archive settings if Mai Content Archives images were not set to display.
* Fixed: Some default settings were getting changed when updating/saving via Genesis > Theme Settings.
* Fixed: Jumpy slider on IE11. Bumped Slick to 1.8.0.

#### 1.1.3.1 (9/14/17)
* Fixed: Header/Footer scripts getting slashed when updating settings via the Customizer.

#### 1.1.3 (9/14/17)
* Fixed: Critical bug where saving Genesis > Theme Settings were resetting all custom settings.
* Fixed: Hide Featured Image setting unable to save as unchecked after saving as checked.
* Fixed: Hide Featured Image setting not hiding image on WooCommerce products.

#### 1.1.2 (9/13/17)
* Added: [grid] New "image_align" parameter. Accepts left, center, or right. This allows [grid] to display content exactly like default archives (e.g. the blog).
* Added: [col] New "bg" parameter. Accepts hex value. Example: [col bg="#000000"].
* Added: [grid] [col] New "bottom" parameter. This allows you to define the bottom margin (spacing) on each entry/column. Example: [col bottom="10"]. This would add 10px of margin to the bottom. Valid values are 0, 5, 10, 20, 30, 40, 50, 60.
* Changed: Updated plugin-update-checker to latest version (4.2).
* Fixed: Posts per page setting not working on CPT archives.
* Fixed: Max width on entry pagination images when images weren't regenerated after activating Mai Pro.

#### 1.1.1 (9/11/17)
* Changed: Allow borders to show around flex-entry images.
* Changed: Site header padding consistency.
* Fixed: Sidebar order on Sidebar-Content layout.
* Fixed: Hiding featured image on a page/post not saving correctly.

#### 1.1.0.1 (9/8/17)
* Fixed: [grid] slider jump when scrolling/swiping a slider partially out of the viewport.

#### 1.1.0 (9/8/17)
* Added: Post type specific settings: Default layouts, auto-display feaured image, hide banner, and much more.
* Changed: Move core settings from theme_mods and metaboxes to options in the Customizer. Hooray, live previews!
* Changed: Sections page template now also saves section content to 'content' column in the DB, for search indexing and SEO analysis (via Yoast/etc).
* Changed: [grid] slidestoscroll now defaults to the amount of columns in the grid.
* Fixed: Site header not using h1 on front page.
* Fixed: Various other minor bug fixes:

#### 1.0.16 (8/14/17)
* Changed: The mai_get_read_more_link() function now fires inside the loop and gives access to more data.
* Changed: Now using genesis_attr filter for more-link element, for more control and filterable attributes.
* Changed: [grid] When add_to_cart="true", only the Add To Cart button adds product to cart, image/title link to the product itself.
* Fixed: Issue when a form is in Woo short description.

#### 1.0.15.1 (8/10/17)
* Fixed: Sections template fields weren't full width.

#### 1.0.15 (8/10/17)
* Changed: Sections metabox is now displayed directly after the title field.
* Changed: Update CMB2 to 2.2.5.2.
* Fixed: Error when /%category%/ was used in permalinks and your visit a 404's url.
* Fixed: Sub-menu text alignment when text wraps to a second line.

#### 1.0.14 (8/7/17)
* Changed: Better blockquote styling.
* Changed: Remove excess grid top margin.
* Changed: Sections template now sanitizes WYSIWYG editor value the same as WP.

#### 1.0.13.1 (8/1/17)
* Fixed: Page template loader no longer runs on all pages. More efficient and fixes potential conflicts. Props @timothyjensen.

#### 1.0.13 (8/1/17)
* Added: [grid] New filter on default args so developers can change the default settings for the shortcode.
* Changed: Better blockquote styling.
* Changed: Better button style handling, especially with WooCommerce buttons.
* Fixed: [grid] Image markup when image_location is before_entry link is false.
* Fixed: [grid] Slider dot color when on dark background.
* Fixed: mai_get_grid() helper function unnecessarily requiring $content param.

#### 1.0.12.2 (7/28/17)
* Changed: Add bottom margin to galleries.
* Fixed: Better browser support for gradient overlay.

#### 1.0.12.1 (7/27/17)
* Changed: More efficient fix for removing empty <p> tags from shortcodes in widgets.

#### 1.0.12 (7/26/17)
* Added: [grid] can now 'exclude_categories' from display. Example: Display all posts except those in the 'Recipes' category.
* Fixed: [grid] Center slider dots.

#### 1.0.11 (7/24/17)
* Changed: Hierarchical taxonomy terms now check parents all the way up the tree for any archive settings (props @hellofromTonya).

#### 1.0.10
* Added: Setting to disable term archives by taxonomy.

#### 1.0.9.1
* Fixed: [grid] was not linking correctly when displaying taxonomy terms with image_location="bg".

#### 1.0.9
* Added: Setting to allow featured images to be used as the banner image.
* Added: Child category/taxonomy archives now fallback to their parent term banner image (up to 4 levels deep).
* Added: [grid] slider can now autoplay via autoplay="true" and adjust autoplay speed with speed="3000".

#### 1.0.8
* Added: Entry pagination now shows a 'tiny' thumbnail.
* Fixed: Mai Pro front page now works as expected if set to display latest posts.
* Fixed: Featured image caption display if featured image is set to auto-display.

#### 1.0.7.2
* Fixed: Error when running PHP 5.3.

#### 1.0.7.1
* Fixed: Entry meta spacing.

#### 1.0.7
* Added: You can now align featured images left or right when post archives are in columns.
* Added: Default favicon.
* Changed: Odd sections now have a white background as a default.
* Fixed: Center logo on logo screen.
* Fixed: Section content alignment on Safari 7/8 and IE11.
* Fixed: WooCommerce notice content alignment.

#### 1.0.6
* Added: Screen reader text to read more links in [grid].
* Changed: Use dedicated anchor link for flex loop and grid entry bg links.

#### 1.0.5
* Added: Entry header/content/footer filters to [grid] shortcode entries.
* Fixed: Remove nested links when showing excerpts or full content in archive flex loop.
* Fixed: Remove redundent conditional checks in flex loop.
* Fixed: Better archive setting check for Woo custom taxos.
* Fixed: FacetWP arg is no longer true by default.
* Fixed: Extra entry pagination margin.
* Fixed: Table styling.
* FIxed: Mobile menu toggle won't shrink if logo is big on smaller screens.

#### 1.0.4
* Changed: Refactor archive settings output functions.
* Changed: Allow menu itmes to wrap on primary/secondary nav.
* Fixed: Cleanup tabs/spaces.

#### 1.0.3
* Added: Banner alignment setting.

#### 1.0.2.1
* Fixed: z-index issue on sections template prohibiting editing of some fields.

#### 1.0.2
* Added: FacetWP support in [grid] shortcode.
* Added: Add additional settings to each section.
* Changed: Move section settings to slide out side panel.

#### 1.0.1.1
* Fixed: Remove unnecessary width declaration on img.

#### 1.0.1
* Fixed: IE fix for full width section wrap not centering.

#### 1.0.0
* Initial release.
