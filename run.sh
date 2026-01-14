#!/bin/bash
cd public && php -d apc.enable_cli=1 -S localhost:8000
