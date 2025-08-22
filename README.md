# Lab2Gpx

online tool to generate gpx from Adventure Labs.

## Cache ID generation

This version of the tool does no longer use the code from the
FirebaseDynamicLink as cache ID, because this link does no longer
exist in the API. Instead it generates an unique base32 encoded ID,
which is stored in an sqlite database.
So you now need sqlite and pdo extensions of PHP.
