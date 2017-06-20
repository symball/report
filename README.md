ReportBundle
============

The bundles provides a suite of tools designed to speed up the creation of Excel style reports with Symfony whilst not forcing you to do things in one way. Features include:

- An abstraction layer for writing content to [PHPExcel](https://github.com/PHPOffice/PHPExcel) that spans multiple sheets
- A navigation handling service for pointer movement and multiple object / set tracking
- A data broker which acts as a single point for data preperation prior to rendering
- A database query handler for defining information retrieval
- A pattern service which allows for write once, reuse many times for common report features
- A style service for adding presentation to the spreadsheet such as different font styles, BG, borders, etc.
- Outputting the report to all formats supported by PHPExcel returning a Symfony file object for further manipulation. E.G Upload to Amazon S3 using Gaufrette

This bundle was conceived due to a project that required the creation of many reports and there being a current shortage of bundles to fulfill the need. Rather than choosing to repeat the same code over, I made a wrapper with some simple utility functions and, before I knew it, after making it flexible enough to support various features of a report, had a bundle.

---

Installation
------------

Require the bundle with Composer
``` bash
composer require symball/report
```
Enable the bundle within your Kernel space
``` php
public function registerBundles()
{
    $bundles = [
        // ...
        new Symball\ReportBundle\SymballReportBundle(),
        // ...
    ];
    ...
}
```
Set the save path where your reports will go within your config
``` yaml
symball_report:
    default_report_path: "%kernel.root_dir%/../var/reports"
```

---

Usage
-----

The collection of services provided by this bundle can be used independently but, to get the most out of it you should use the facade service which is available from the container using the following name
``` php
$reportBuilder = $container->get('symball_report.report_builder');
```
Due to the fact that reports might need to process a lot of data, it is recommended to define your report within a Symfony command to ensure the request doesn't time out.

Report Layout
-------------

The data broker service is accessed through the meta function. This service is responsible for setting high level options and tracking

``` php
$reportBuilder->meta()
```

## Options

Any "options" that you will use in your report are handled by the meta service and made available to the other services or, in the case of using the pattern or style service, automatically included. See the options section for information on some of the options that are used in the standard distribution

``` php
$reportBuilder->meta()
// Set a single option.
->setOption('option_key', false)
// Set multiple options from array
->setOptions(['option_key' => 'option_value',])
// Retrieve the value of a single option
->getOption('option_key')
// Retrieve all options
->getOptions()
```

## Column definitions

In order of argument defintion

- Internal reference for setting / accessing data. For presentation, if no title is set in the options argument, then underscores are replaced with spaces and words are capitalised.
- *optional* Default column value. If empty, value is empty string.
- *optional* Array of columnn options.

``` php
// String column
->column('column_reference')
// Numeric column
->column('column_reference', 0)
// String column With custom default value
->column('column_reference', 'NA')
// Custom column heading
->column('column_reference', 'NA', ['title' => 'My custom heading']
// Setting some display options which will be parsed at render time
->column('column_reference', 0, ['display_options' => ['highlight_negative']])
```

Data Collection
===============

## Query Definition

** note ** - This feature is currently quite basic and will be upgraded.

The query service provides a method to define how the raw data to be used in your report is collected. The objective is to create models of how various reports will function across multiple data sets such as time interval based reports.

The first form of this service (which others will inherit from and add methods to) currently functions as follows:

``` php
// Create the query object you will use
$query = new Query();
$query->setRepository($aDoctrineRepository)
// Inform the query object that the report will span multiple data sets. 1 by default
$query->setNumberDataSets(3)
// Optionally define a base query (currently Doctrine Query Builder) to be present across all data sets
// If not used, will try to fetch all
$query->setQueryBase($doctrine->createQueryBuilder()->field('status')->equals('active'));
// When using multiple data sets, refine search parameters across the sets. Used within the "set loop"
$query->addModifier('database_field', ['type' => 'equals', 'value' => 'example_value']);
```

There is also a query type designed to handle reports that handle time ranges which extends the basic query service as defined above with some additional methods and behaviour.

``` php
// Create the query object you will use
$query = new QueryTimeInterval();
// When to start
$query->setHeadDateTime(new \DateTime());
// Time that each set will span
$query->setIntervalDateTime(new \DateInterval('P1W'));

```

Data Processing
===============

## The Set Loop

In order to make data on a report meaningful, it typically requires some form of data processing. The set loop provides:

- Handling multiple data sets with minimal code
- A place where data points (x axis) have their positions tracked removing the need for you to consider where new points might be
- Values are reset according to the column definition.

``` php
// If only using a single set
$reportBuilder->newSet();
$unprocessedData = $reportBuilder->query()->run();

// For handling multiple sets
while($reportBuilder->newSet()) {
    $unprocessedData = $reportBuilder->query()->run();
}
```

With the above code, you may notice that the data is only set to a variable called unprocessedData; this is a Doctrine collection which you can loop over. Getting the report builder to recognise the data requires using the data broker which provides some utilities for keeping things simple

``` php
foreach($unprocessedData as $dataElement) {

    // Determine whether this element is overdue or not
    $aProcessedValue = ($dataElement->getValue() > 70) ? 'pass' : 'fail';

    $reportBuilder->meta()
    // Set a point of reference where data will be stored. Internally, this is stored as a string so if you pass an
    // object, it will try stringify it
    ->setPoint($dataElement)
    // Set the value of a column
    ->set('column_reference', $dataElement->getValue())
    // Use the processed value
    ->set('column_reference', $aProcessedValue)
    // Increment the value of a column by 1
    ->increment('column_reference')
    // Increment the value of a column by custom amount
    ->increment('column_reference', $dataElement->getValue())
}
```

You might be creating a report which groups data together such as sales per branch. This is why *setPoint* and *increment* exist giving you a way to modify existing entries.

When data points have not yet been defined, they will be added to to the data broker and tracked thereafter. It is not necessary to concern yourself with positioning if (for example) you are 100 sets in and then add new data points, they will simply be displayed on the end, not affecting the position of any previous sets.

** Note ** - It is possible to define data points manually using ``` $meta->addPoint('reference'); ```

Simple Rendering
================


Whilst the report builder provides utilities to aid populating a spreadsheet with content, there is a *pattern service* which wraps predefined workflow for common features in to a single line. Patterns are tagged services which take the reportBuilder as an argument.

``` php
$reportPattern = $container->get('symball_report.pattern');
// Get a simple array list of available patterns
$availablePatterns = $reportPattern->getPatternsLoaded();
// Draw column headings for the current data set
$reportPattern->run('set_headings', $reportBuilder);
// Fill in the data set values
$reportPattern->run('data_set', $reportBuilder);
// Draw the data point index (x axis)
$reportPattern->run('data_point_index', $reportBuilder);
```

## Creating a new pattern

If you plan on creating a new render pattern, it needs to be a tagged service and implement the Pattern interface.

``` php
use Symball\ReportBundle\Service\ReportBuilder;
use Symball\ReportBundle\Interfaces\Pattern;

class YourPattern implements Pattern {
    public function run(ReportBuilder &$context) {
        // Do something with the report builder
    }
}
```

``` yaml
# In your services configuration
report_pattern.data_point_index:
    class: AppBundle\ReportPatterns\YourPatter
    tags:
        - { name: symball_report.pattern, alias: your_pattern_reference
```

With the pattern defined, it is a matter of using your alias when creating the report. Using the above as an example:

``` php
$reportPattern->run('your_pattern_reference', $reportBuilder);
```

Manual Control
==============

If planning to populate a spreadsheet manually, it is recommended that you peruse some of the existing patterns in order to understand how things are tied together. There are 3 classes which provide you with a variety of functionality for populating a spreadsheet

## ReportBuilder

The main report builder service itself has a function for writing content to the cell where the navigation pointer currently is

``` php
// Write a value
$reportBuilder->write('some_value');
// Write a formula
$reportBuilder->write('=(1+1)');
// Write a custom formula
// The formula writer will be redesigned
$reportBuilder->formula(1+1);
// Use one of the predefined formulae. This one totals up all values going up the x axis for the current column
$reportBuilder->formula('sum_up_data');
```

## Navigation

The navigation service is used to control the pointer which abstracts row and column information in to a numeric index whilst providing utility functions which make pointer tracking human friendly and stopping the pointer from doing things such as going out of bounds.

Navigation services are interchangable and, if using the human friendly syntax, can automatically adjust how the pointer will move around the spreadsheet. This means you could have data points flowing horizontally, vertically or even with spacing which crosses multiple fields instead of one. By default, the navigation service which has data points flowing vertically and column headings spread horizontally is used.

``` php
$nav = $reportBuilder->nav();
// BASIC CONTROLS
// Display the current coordinates in spreadsheet format
(string) $nav
$nav->coord()
// Move down 1 cell
$nav->down()
// Move right 5 cells
->right(5)
// Move up 1 cell
->up()
// Move left 5 cells
->left(5)
// Move along the x axis 1 cell
->axisXMove()
// Move along the y axis 5 cells
->axisYMove(5)
```

### Human friendly syntax

Within the nav service, when referencing either the row, column, or both as well as being able to use a numerical index there are also 3 string values which will return coordinates according to context. These are:

- *current* refers to where the pointer is now
- *set* When moving through data points and / or data sets, this refers to the start of the current tracked position. The examples below don't take in to account space for headings
    - If you have a report with 2 columns and are on the 3rd set, requesting the set column would return 6.
    - If you have a report with 100 data points and are on the 50th set, requesting the set row would return 50
- *initial* refers to the starting row /column and will never change. This is useful when moving across data sets where you would need to move the pointer to the first row

With the above in mind:
``` php
// Change the current column set value. This is automatically called when using the *newSet* function
$nav->movePointerAlong($this->meta->columnCount())
// Get the starting coordinates for the current data set
$nav->coord('initial', 'set')
// Move the row pointer to where the spreadsheet expects data to start
$nav->rowReset('initial')
// Move the column pointer to start of the current set
$nav->columnReset('set');
```

** Note ** - When it comes to coordinates, excel spreadsheets will use alphabetical indexes to depict the colum such as A or AG. The nav service uses a numerical index and when stringified will convert automatically for you. When manually retrieving the column, if you need the alphabetical index, you need to pass true to the second parameter or convert manually.

``` php
// A1
(string) $nav
// Will have 1
$nav->column()
$nav->column('current')
// Will have A
$nav->column('', true)
$nav->column(false, true
$nav->column('current', true)
$nav->nmbToClm($nav->column())
```

## Styling

The styling service is accessed through the reportBuilder service itself due to the way it is used in various contexts but, from a design and operational point of view, is similar to the pattern builder. Any options you have defined in the meta service will be passed to the style service when a function is called (to have a good set of defaults) but, can be overridden and extended using the third parameter.

Due to the nature of styling (the fact that ranges can be used) it is necessary to pass a coordinate string as the second parameter.

``` php
// Apply a background (using the default background colour) to current location
$reportBuilder->style('bg', (string) $reportBuilder->nav());
// Apply a background using a custom colour
$reportBuilder->style('bg', (string) $reportBuilder->nav(), ['color' => 'B0171F']);

$start = $nav->coord('initial', 'current');
// Note the column value manipulation
$end = $nav->coord(($nav->column() - 1), 'current');
// Draw a border along a range
$reportBuilder->style('border', (string) $reportBuilder->nav(), ['color' => 'B0171F']);
```

Saving the report
=================

The excel service has a shortcut which will save your spreadsheet with access through the report builder. By default it will use the path and format as specified in your configuration.
``` php
$fileObject = $reportBuilder->save('filename.xls');
// Save the report to a one off path
$fileObject = $reportBuilder->save('filename.xls', '/custom/path');
// Save the report with a one off format
$fileObject = $reportBuilder->save('filename.xls', '', 'Excel5');
```