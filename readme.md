# SEO Analysis Project

This project provides an API layer for analyzing SEO-related information of web pages. It utilizes a RESTful API, stores data in an SQL-based database, and includes a background job for periodic rechecking of web page URLs.

## Overview

This project aims to provide SEO insights into web pages through a RESTful API. It includes features such as:

- Analyzing SEO issues for a given URL.
- Periodically rechecking web pages and updating information in the database.
- Generating preview images for web pages.Overview

## Requirements

- PHP (version 8.3.0)
- Symfony (version 7.0.1)
- SQL-based database (e.g., MySQL, PostgreSQL, SQLite)
- Additional php libraries necessary for functionality

## Installation

- Clone the repository: 
```
git clone git@github.com:adeel-ahmad-awan/SEO-Analysis-API.git
```
- Install dependencies: 'composer install' 
- Configure the database:
```
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```
- Install additional php libraries necessary for functionality

## API Endpoints

#### Analyze SEO Information

- Endpoint: /api/analyze
- Method: POST
- Input:
```json
{
  "url": "https://example.com"
}
```
- Output:
```json
{
  "Url": "https://example.com",
  "title": "Example Title",
  "description": "Example Description",
  "issues": ["Issue 1", "Issue 2"],
  "meta tags": {"tag1": "value1", "tag2": "value2"},
}
```

#### Preview Route

- Endpoint: /preview
- Method: GET
- Input: Form submission with URL
- Output: Preview image of the web page

## Background Job

A background job app:recheck-webpages is available to periodically recheck all webpage URLs and update information in the database every hour.
You can run the job using the following command:

```
php bin/console app:recheck-webpages
```

## Author
- Adeel Ahmad 
- adeelahmadawan@gmail.com
