=== Accordion Shortcodes ===
Contributors: doubleedesign, philbuchanan
Author URI: http://www.doubleedesign.com.au
Donate Link: http://philbuchanan.com/
Tags: accordion, accordions, foundation
Requires at least: 3.3
Tested up to: 4.8
Stable tag: 0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Shortcodes for creating responsive accordion drop-downs in Zurb Foundation-based themes.

== Description ==

Foundation Accordion Shortcodes is a simple plugin for Zurb Foundation-based themes that adds a few shortcodes for adding accordion drop-downs to your pages.

This plugin is a fork of Phil Buchanan's Accordion Shortcodes. Changes include:
* Ensured the markup matches Foundation's requirements
* Removed the JavaScript as it is assumed that you are using Foundation's and have included the accordion component
* Removed the option to have the accordion title be a <p> or a <div>, added the option for it to be a <span>
* Changed the class of an initially open item to "is-active" (as per Foundation) rather than "open"; closed items do not have a class
* For now, removed the option to set a heading tag as the item title, as I just can't get it to work with Foundation; I'm working on it and hope to bring it back.

If you are looking for accordion functionality for a non-Foundation theme, then you want Phil's original plugin. If you switch from a Foundation theme to a non-Foundation one, you should be able to swtich from this plugin to the original and maintain your accordion functionality without needing to edit your shortcodes.

This is a work in progress, more testing and tweaking is needed.

= The Shortcodes =

The two shortcodes that are added are:

`[accordion]...[/accordion]`

and

`[accordion-item title=""]...[/accordion-item]`

= Basic Usage Example =

    [accordion]
    [accordion-item title="Title of accordion item"]Drop-down content goes here.[/accordion-item]
    [accordion-item title="Second accordion item"]Drop-down content goes here.[/accordion-item]
    [/accordion]

This will output the following HTML:

    <ul class="accordion" data-accordion data-allow-all-closed="true">
		<li class="accordion-item" data-accordion-item>
			<a class="accordion-title"><span>Title of accordion item</span></a>
			<div class="accordion-content">
				Drop-down content goes here.
			</div>
		</li>
        <li class="accordion-item" data-accordion-item>
			<a class="accordion-title"><span>Second accordion item</span></a>
			<div class="accordion-content">
				Drop-down content goes here.
			</div>
		</li>
    </div>

== Installation ==
1. Upload the folder to the '/wp-content/plugins/' directory.
2. Activate the plugin through the Plugins menu in WordPress.
3. Add the shortcodes to your content.