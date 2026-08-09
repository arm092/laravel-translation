#!/usr/bin/env bash

set -u

max_attempts=3

for attempt in $(seq 1 "$max_attempts"); do
    if composer audit; then
        exit 0
    fi

    if [ "$attempt" -eq "$max_attempts" ]; then
        echo "Composer advisory service remained unreachable; performing the final audit with --ignore-unreachable." >&2
        composer audit --ignore-unreachable
        exit $?
    fi

    echo "Composer audit failed on attempt ${attempt}; retrying after a short delay." >&2
    sleep $((attempt * 5))
done
