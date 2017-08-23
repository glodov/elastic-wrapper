# elastic-wrapper
Elastic search PHP wrapper for MVC frameworks

## Example of usage

Run command `php tests/index.php` to make indexed database of cars taken from [automotive-model-year-data](https://github.com/n8barr/automotive-model-year-data).

Run command `php tests/search.php` to see how search function works. Second initialization of $search = new Search() is required, otherwise it returns old results (from previous search matches), I did not find out why.