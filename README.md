# ConClár from AirTable

This is a simple PHP script to extract data from AirTable and write to
`programme.json` and `people.json` for use by ConClár.

## Usage

After extracting the repository:

1. Run `composer install` to download dependency.
2. Copy `settings.example.php` to `settings.php`.
3. Edit `settings.php` to insert API key and IDs.
4. Can be run from command line with `php airtable_extract`.
5. Can be added to `crontab` to rebuild regularly, but remember to remove when
   no longer needed to minimise server load.

## Author

Developed by James Shields (lostcarpark) and released under the MIT licence.
