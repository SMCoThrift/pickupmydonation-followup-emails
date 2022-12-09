# PickUpMyDonation.com Followup Emails
**Contributors:** [thewebist](https://profiles.wordpress.org/thewebist/)
**Stable tag:** 1.0.0
**License:** GPLv2 or later
**License URI:** https://www.gnu.org/licenses/gpl-2.0.html

Sends followup emails to donors to PickUpMyDonation.com.

## Crontab

Setup a cron to run this script like so:

```
*/15 * * * * cd /path/to/pmd/webroot/public; /usr/local/bin/wp eval-file ../followup-emails/follow-up-emails.php > /path/to/this/repo/followup-emails/emails.log 2>&1
```

## Changelog

### 1.0.0
* Initial release.