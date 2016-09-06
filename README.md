# CONcrescent
CONcrescent is the fandom convention registration system.

CONcrescent has been used successfully to run BABSCon, the second-largest My Little Pony convention, every year since 2015.

## Features
CONcrescent provides everything a fandom convention needs in a registration and HR system, including:
*   Attendee registration
*   Vendor and artist applications
*   Panelist and event applications
*   Community guest applications
*   Press/media applications
*   Staff applications
*   Registration check-in and badge printing
*   Registration and application statistics with line chart
*   Fast searching over records with advanced search syntax
*   Multiple badge types
*   Customizable registration and application forms
*   Promo/discount codes for attendees
*   Blacklisting
*   Customizable email notifications
*   CSV export
*   Vendor/artist table management
*   Panel scheduling
*   Interview scheduling
*   Department hierarchy management
*   Staff org chart generation
*   Staff mailing list generation
*   Badge artwork management and layout
*   Badge preprinting
*   Payment requests
*   Multiple admin user accounts
*   Integration with PayPal for payment
*   Integration with Slack for notifications

## Installation
1.  Check out the `cm2` directory.
2.  Edit `cm2/config/config.php`, replacing the default values with values
    specific to your web server, PayPal account, and event. Also make sure
    to **change the default admin username and password**.
3.  Upload the `cm2` directory to your web server.
4.  Log in to `cm2/admin/` with the username and password set in the configuration file in step 2.
5.  Go through each section in the side nav to set up badge types, form questions, blacklists,
    email notifications, rooms and tables, departments, badge artwork, admin accounts, etc.

You may rename the `cm2` directory to anything you like, move it to a subdirectory,
or even move the contents of the `cm2` directory into the root of your web server.
CONcrescent will figure out where it is installed and generate appropriate URLs
without needing to be told.
