# jsonapi-serializer
A PHP Serializer following the JSON API specification.

http://jsonapi.org/format/

Entity and Field Metadata:
- Defines how an entire entity and it's fields should be serialized
- Defines how data should be formatted
- Instructs how the data will be converted into JSON API spec format

Handled data formats:
- An associative array
- An object with public properties
- An object with getters/setters
