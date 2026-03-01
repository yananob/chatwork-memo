#!/bin/bash
set -eu

gcloud pubsub topics publish chatwork-memo-event --message='{"command": "batch-update-wishlist"}'
