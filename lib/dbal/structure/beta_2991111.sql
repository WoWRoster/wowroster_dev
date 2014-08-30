#
# MySQL WoWRoster Upgrade File
#
# * $Id: upgrade_230.sql 2632 2014-08-21 20:28:28Z ulminia@gmail.com $
#
# --------------------------------------------------------
### New Tables

# --------------------------------------------------------
### Altered Tables

# --------------------------------------------------------
### Add to Tables

# --------------------------------------------------------
### Update Tables
# --------------------------------------------------------
### Config Table Updates

# javascript/css aggregation

### api key settings
# session settings
# --------------------------------------------------------
### Menu Updates
INSERT INTO `renprefix_menu_button` VALUES (3, 0, 'menu_roster_ucp', 'util', 'ucp', 'inv_misc_gear_07');
