Nanocoatings language package

Structure:
- en/: English language files
- vi/: Vietnamese language files

Main changes:
- Replaced old CASUMINA/Casumina/Advenza display text with Nanocoatings context.
- Rewrote tire/rubber-specific content into nano coating, surface protection, waterproofing, anti-corrosion, easy-clean and industrial coating language.
- Changed dealer-heavy wording into partner/showroom/technical partner wording.
- Changed warranty-heavy wording into support request wording where appropriate.
- Kept original Laravel translation keys unchanged to avoid breaking calls like __('messages.about_casumina').
- Kept validation, pagination and password text generic where no project-specific wording was needed.

Notes:
- Please update company_name and company_address with the official Nanocoatings legal name and address before production.
- All PHP files passed php -l syntax check.
