Installation
============
1. Upload and activate plugin

2. Go to Group Buying > Add-ons, and active this add-on.

3. Go to Appearance > Menus, and add a Custom Link to your menu using the URL http://your-site.com/merchant/submit-deal/?suggestion=1 where "your-site.com" is your site's address. Enter "Suggest a deal" for the Label and hit the "Add to Menu" link. Save the menu.

4. Open a page on the front end of your site and click on the newly created "Suggest a deal" link in your menu. Create newly suggested deal by completing this form.

5. Go to the backend of your site and click on the Deals link. All suggested deals will appear as drafts. Locate the suggested deal and click the Edit link. Scroll to the bottom of the deal page, check the box for "This is a suggested deal.", and hit the publish button. This will make the suggested deal appear on the suggested deals page (i.e. http://your-site.com/suggested/deals/).

5a. Set the deal to be expired so that it isn't returned in functions looking for the latest deal.

6. Add a menu link for the "Suggested Deals" page like you did for step 3, i.e. http://your-site.com/suggested/deals/.

If you want to run a suggested deal as a live deal, simply uncheck that box for "Is a Suggested Deal", modify the deal's settings (price, publication date, locations, etc.) and hit the update button. It will then display as a live deal so customers can purchase it.

To convert a suggestion to a deal
---------------------------------
Simply edit the deal, then unselect the 'This is a suggested deal.' checkbox under the "Suggestions" box. Make sure to edit all settings, including the expiration date.


To create a suggestion via the admin
------------------------------------
Simply edit the deal, select the 'This is a suggested deal.' checkbox under the "Suggestions" box in the sidebar, and finally publish.

Send Notifications
------------------

Check the box for "Publish and send all "suggested published" notifications.", this will automatically uncheck the "This is a suggested deal." option (since at that point it would be no longer a suggestion and instead be released as a deal).

Notes
-----

Suggestions simplifies the default deal submission, just link to the deal submission url with an added piece to the url (?suggestion=1). For example,
http://yoursite.com/merchant/submit-deal/?suggestion=1
Note: The merchant/submit-deal/ is customizable in your GBS options

All suggestions are added and marked as drafts.

Once published the suggestions will show via - http://yoursite.com/suggested/deals/
Note: The suggested deals will be removed from your main deal loops.

Users will be able to vote up a suggestion only once.


Template Modifications
----------------------

Templates can be overridden in your child theme. Just follow the same file structure found in this plugin/add-on.

Suggestions Loop Example, [child-theme]/gbs/deals/suggestions.php 
Suggestions Loop Content Example, [child-theme]/inc/loop-suggestion.php

