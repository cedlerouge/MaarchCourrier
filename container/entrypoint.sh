#!/bin/sh

# Exit on error
set -e

# Set correct umask for container
umask 0077

# Start container
exec "$@"
