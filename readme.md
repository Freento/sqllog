# User Guide for Freento SQL Log Module for Magento 2

## Introduction

The Freento SQL Log Module for Magento 2 offers advanced capabilities for logging SQL queries, simplifying the analysis and optimization of database interactions. Here's a straightforward overview:

1. **Logging in Elastic:**
    - Gathers detailed information about SQL queries, including trace data and execution times, storing it in an Elastic database.

2. **Requests Separation:**
    - Each request, AJAX call, CLI command execution will be separated and displayed as a single record.

3. **Easy Administration:**
    - Manages SQL logging directly from the Magento admin panel with simple clicks for enabling or disabling logging.

4. **Advanced analysis directly from Magento admin:**
    - Accesses a straightforward admin interface to view logged queries. Utilizes filters and sorting options for easy result analysis.

5. **Detailed Query Insight:**
    - Delves into SQL query details, exploring specifics like query type, execution time, and start time for effective resolution of performance issues, with ability to filter and search by specific parameters.

6. **Selective Logging Rules:**
    - Sets rules for selective logging, allowing the choice of specific pages, requests, or CLI commands. This flexibility facilitates targeted analysis and troubleshooting.

Gain control and insight into your Magento database interactions with the Freento SQL Log Module, making performance optimization more accessible. For any questions, refer to the documentation or seek help our development team: https://freento.com/contact/

---

## Installation

Usual [Magento module installation steps](https://experienceleague.adobe.com/docs/commerce-operations/installation-guide/tutorials/extensions.html?lang=en) using composer

    composer require freento/sqllog

## Use Cases

__Note: Using the Freento SQL Log Module on production servers is not recommended due to potential performance impacts__

### How to work with the module on local environment:

1. Enable logging in the local environment:

Admin > FPS > Sql Log > Configuraion > General > Enable for web = Yes.
Ensure that Full Page Cache is disabled in the working environment.

2. Open several pages on store.
3. Open FPS > SQL Log > SQL Requests Log. Explore pages with more than 100-200 queries.
4. Analyze these pages to identify potential optimizations. Analyze queries, stack trace and other quantitative indicators of query execution.

### Things that can be additionally tested

Focus on actions related to adding items to the cart, as well as background tasks (cron jobs, queue).

1. Add to cart AJAX action.
2. Cron jobs.
3. Queue.

This will help identify potential bottlenecks and optimize them.

### Usage in production
__Note: Using the Freento SQL Log Module on production servers is not recommended due to potential performance impacts. In case of absolute necessity, follow the recommendations below.__

Limit usage to less frequently accessed pages:
If enabling logging on a production server is necessary, consider activating it only on pages with low activity.
Use regular expressions (REGEXP) to filter URL addresses and selectively enable logging using Allowed URLs feature.
Example regular expression for filtering by ?test-sqllog-debug=1 parameter in the URL:

``` 
    .*\?test-sqllog-debug=1
``` 

This regular expression allows enabling logging only on pages where the URL contains the parameter test-sqllog-debug=1.

## Configuration

### General Settings

1. **Prepare Elastic:**
    - Click on the "Prepare Elastic" button to populate the `var/fps/sql-log.json` file with Elastic connection data, create or recreate the Elastic index, and clear module-related Elastic DB data.

2. **Enable for Web:**
    - Select "Yes" to enable SQL request collection on web pages (frontend, backend).
    - If selected, additional options for "Allowed URLs" and "Disallowed URLs" will be available.

    - **Allowed URLs:**
        - Enter regular expressions to specify allowed URLs. Examples are provided for guidance (you can check them here: https://regex101.com/)
      ``` 
          ^\/admin\/ - admin pages with front name "admin"
          ^\/checkout\/($|#|\?) - default magento checkout page
          ^\/media - media (e.g. images)
          ^\/static - static content
          ^\/($|\?|#) - home page
          ^\/page_name\.html
          ^\/category_page\/product_page\.html
      ```

    - **Disallowed URLs:**
        - Enter regular expressions to specify disallowed URLs. Examples are provided for guidance (you can check them here: https://regex101.com/)
      ``` 
          ^\/admin\/ - admin pages with front name "admin"
          ^\/checkout\/($|#|\?) - default magento checkout page
          ^\/media - media (e.g. images)
          ^\/static - static content
          ^\/($|\?|#) - home page
      ```

3. **Enable in CLI:**
    - Select "Yes" to enable SQL request collection in CLI (e.g., cron, queue, bin/magento).
    - If selected, additional options for "Allowed Commands" and "Disallowed Commands" will be available.

    - **Allowed Commands:**
        - Enter regular expressions to specify allowed CLI commands. Examples are provided for guidance(you can check them here: https://regex101.com/ )
      ``` 
          queue:consumers:start - queue start
          setup: - all from setup namespace
          sys:cron - all cron commands (e.g. run)
      ```

    - **Disallowed Commands:**
        - Enter regular expressions to specify disallowed CLI commands. Examples are provided for guidance(you can check them here: https://regex101.com/ )
      ``` 
          queue:consumers:start - queue start
          setup: - all from setup namespace
          sys:cron - all cron commands (e.g. run)
      ```

### Web Page Logging

- Web page logging is conditional on the "Enable for Web" setting.
- If enabled, SQL queries will be logged based on the specified URL patterns.

### CLI Logging

- CLI logging is conditional on the "Enable in CLI" setting.
- If enabled, SQL queries will be logged based on the specified CLI command patterns.

---

## SQL Requests Logging

### SQL Requests Log

1. Navigate to "FPS > SQL Log > SQL Requests Log." On this page you can find out which actions are causing too many database queries.
2. View a list of logged actions (web requests or CLI commands) sorted by date.

    - **Columns:**
        - Action Path (sortable, filterable)
        - Request Date (sortable, filterable)
        - Number of Queries (sortable, filterable)

3. Click on "Detailed queries" weblink in "Action" column to view detailed information.

### Detailed Queries

1. Within an action, view detailed SQL queries by clicking "Detailed Queries." On this page you can find out which database queries are taking too long to complete.
2. View information about each SQL query.

    - **Columns:**
        - Type (sortable, filterable)
        - Query (sortable, filterable)
        - Executing Time (sortable, filterable)
        - Start Time (sortable, filterable)
      
3. Click on "Trace" weblink in "Action" column to view detailed information.

### Trace Information

1. Within a detailed query, click "Trace" to view trace information. On this page you can find out the place in the code where the request of interest is executed.
2. View details about the trace leading to the SQL query.

    - **Columns:**
        - Id (sortable)
        - File (sortable)
        - Code (sortable)
        - Line (sortable)

