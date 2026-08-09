#!/usr/bin/env bash

set -u

max_attempts=3

for attempt in $(seq 1 "$max_attempts"); do
    if composer audit; then
        exit 0
    else
        exit_code=$?
    fi

    if [ "$attempt" -eq "$max_attempts" ]; then
        exit "$exit_code"
    fi

    echo "Composer audit failed on attempt ${attempt}; retrying after a short delay." >&2
    sleep $((attempt * 5))
done
