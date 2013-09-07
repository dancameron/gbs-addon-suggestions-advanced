Installation
============
1. Upload and activate plugin

2. Go to Group Buying > General Options, scroll to bottom of page and check the box for Deal Suggestions and hit the save changes button

3. Go to Appearance > Menus, and add a Custom Link to your menu using the URL http://your-site.com/merchant/submit-deal/?suggestion=1 where "your-site.com" is your site's address. Enter "Suggest a deal" for the Label and hit the "Add to Menu" link. Save the menu.

4. Open a page on the front end of your site and click on the newly created "Suggest a deal" link in your menu. Create newly suggested deal by completing this form.

5. Go to the backend of your site and click on the Deals link. All suggested deals will appear as drafts. Locate the suggested deal and click the Edit link. Scroll to the bottom of the deal page, check the box for "This is a suggested deal.", and hit the publish button. This will make the suggested deal appear on the suggested deals page (http://your-site.com/suggested/deals/).

6. Add a menu link for the "Suggested Deals" page like you did for step 3

If you want to run a suggested deal as a live deal, simply uncheck that box for "Is a Suggested Deal", modify the deal's settings (price, publication date, locations, etc.) and hit the update button. It will then display as a live deal so customers can purchase it.


Notes
-----

Suggestions simplifies the default deal submission, just link to the deal submission url with an added piece to the url (?suggestion=1). For example,
http://yoursite.com/merchant/submit-deal/?suggestion=1
Note: The merchant/submit-deal/ is customizable in your GBS options

All suggestions are added and marked as drafts.

Once published the suggestions will show via - http://yoursite.com/suggested/deals/
Note: The suggested deals will be removed from your main deal loops.

Users will be able to vote up a suggestion only once. 

To convert a suggestion to a deal
---------------------------------
Simply edit the deal, then unselect the 'This is a suggested deal.' checkbox under the "Suggestions" box.


Template Modifications
----------------------

Templates can be overridden in your child theme. Just follow the same file structure found in this plugin/add-on.

Suggestions Loop Example, [child-theme]/gbs/deals/suggestions.php 
Suggestions Loop Content Example, [child-theme]/inc/loop-suggestion.php

