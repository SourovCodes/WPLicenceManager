# WP Licence Manager API Documentation

**Base URL:** `https://wplicence.jonakyds.com/api`

**Content-Type:** All requests must include `Content-Type: application/json` header.

---

## Table of Contents

- [License Management](#license-management)
  - [Activate License](#activate-license)
  - [Deactivate License](#deactivate-license)
  - [Validate License](#validate-license)
  - [License Status](#license-status)
- [Updates](#updates)
  - [Check for Updates](#check-for-updates)
  - [Download Update](#download-update)
- [Error Handling](#error-handling)

---

## License Management

### Activate License

Activate a license key on a specific domain.

**Endpoint:** `POST /api/license/activate`

**Request Body:**

| Parameter      | Type   | Required | Description                        |
|----------------|--------|----------|------------------------------------|
| `license_key`  | string | Yes      | The license key to activate        |
| `domain`       | string | Yes      | The domain to activate the license on |
| `product_slug` | string | Yes      | The product slug identifier        |

**Example Request:**

```bash
curl -X POST https://wplicence.jonakyds.com/api/license/activate \
  -H "Content-Type: application/json" \
  -d '{
    "license_key": "XXXX-XXXX-XXXX-XXXX",
    "domain": "example.com",
    "product_slug": "my-plugin"
  }'
```

**Success Response (200):**

```json
{
  "success": true,
  "message": "License activated successfully.",
  "license": {
    "status": "active",
    "product": {
      "name": "My Plugin",
      "slug": "my-plugin",
      "version": "1.2.0",
      "has_api_access": false
    },
    "activated_at": "2025-12-29T10:30:00+00:00",
    "expires_at": "2026-12-29T10:30:00+00:00",
    "is_expired": false,
    "active_domain": "example.com",
    "domain_changes_remaining": 2
  },
  "local_key": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
}
```

**Already Activated Response (200):**

```json
{
  "success": true,
  "message": "License is already activated on this domain.",
  "license": {
    "status": "active",
    "product": {
      "name": "My Plugin",
      "slug": "my-plugin",
      "version": "1.2.0",
      "has_api_access": false
    },
    "activated_at": "2025-12-29T10:30:00+00:00",
    "expires_at": "2026-12-29T10:30:00+00:00",
    "is_expired": false,
    "active_domain": "example.com",
    "domain_changes_remaining": 2
  }
}
```

**Error Responses:**

| Status | Message                                              |
|--------|------------------------------------------------------|
| 404    | Product not found.                                   |
| 404    | Invalid license key.                                 |
| 403    | This license has been revoked.                       |
| 403    | This license has expired.                            |
| 403    | Maximum domain changes reached. Please contact support. |

---

### Deactivate License

Deactivate a license from a domain.

**Endpoint:** `POST /api/license/deactivate`

**Request Body:**

| Parameter      | Type   | Required | Description                           |
|----------------|--------|----------|---------------------------------------|
| `license_key`  | string | Yes      | The license key to deactivate         |
| `domain`       | string | Yes      | The domain to deactivate the license from |
| `product_slug` | string | Yes      | The product slug identifier           |

**Example Request:**

```bash
curl -X POST https://wplicence.jonakyds.com/api/license/deactivate \
  -H "Content-Type: application/json" \
  -d '{
    "license_key": "XXXX-XXXX-XXXX-XXXX",
    "domain": "example.com",
    "product_slug": "my-plugin"
  }'
```

**Success Response (200):**

```json
{
  "success": true,
  "message": "License deactivated successfully."
}
```

**Error Responses:**

| Status | Message                                  |
|--------|------------------------------------------|
| 404    | Product not found.                       |
| 404    | Invalid license key.                     |
| 400    | License is not activated on this domain. |

---

### Validate License

Validate if a license is valid for a specific domain.

**Endpoint:** `POST /api/license/validate`

**Request Body:**

| Parameter      | Type   | Required | Description                        |
|----------------|--------|----------|------------------------------------|
| `license_key`  | string | Yes      | The license key to validate        |
| `domain`       | string | Yes      | The domain to validate against     |
| `product_slug` | string | Yes      | The product slug identifier        |

**Example Request:**

```bash
curl -X POST https://wplicence.jonakyds.com/api/license/validate \
  -H "Content-Type: application/json" \
  -d '{
    "license_key": "XXXX-XXXX-XXXX-XXXX",
    "domain": "example.com",
    "product_slug": "my-plugin"
  }'
```

**Valid License Response (200):**

```json
{
  "success": true,
  "valid": true,
  "message": "License is valid.",
  "license": {
    "status": "active",
    "product": {
      "name": "My Plugin",
      "slug": "my-plugin",
      "version": "1.2.0",
      "has_api_access": false
    },
    "activated_at": "2025-12-29T10:30:00+00:00",
    "expires_at": "2026-12-29T10:30:00+00:00",
    "is_expired": false,
    "active_domain": "example.com",
    "domain_changes_remaining": 2
  }
}
```

**Invalid License Response (200):**

```json
{
  "success": true,
  "valid": false,
  "message": "License is not valid for this domain.",
  "license": {
    "status": "active",
    "product": {
      "name": "My Plugin",
      "slug": "my-plugin",
      "version": "1.2.0",
      "has_api_access": false
    },
    "activated_at": "2025-12-29T10:30:00+00:00",
    "expires_at": "2026-12-29T10:30:00+00:00",
    "is_expired": false,
    "active_domain": "other-domain.com",
    "domain_changes_remaining": 2
  }
}
```

**Error Response:**

| Status | Message              |
|--------|----------------------|
| 404    | Product not found.   |
| 404    | Invalid license key. |

---

### License Status

Get detailed status information about a license.

**Endpoint:** `POST /api/license/status`

**Request Body:**

| Parameter      | Type   | Required | Description                 |
|----------------|--------|----------|-----------------------------|
| `license_key`  | string | Yes      | The license key to check    |
| `product_slug` | string | Yes      | The product slug identifier |

**Example Request:**

```bash
curl -X POST https://wplicence.jonakyds.com/api/license/status \
  -H "Content-Type: application/json" \
  -d '{
    "license_key": "XXXX-XXXX-XXXX-XXXX",
    "product_slug": "my-plugin"
  }'
```

**Success Response (200):**

```json
{
  "success": true,
  "license": {
    "status": "active",
    "product": {
      "name": "My Plugin",
      "slug": "my-plugin",
      "version": "1.2.0",
      "has_api_access": false
    },
    "activated_at": "2025-12-29T10:30:00+00:00",
    "expires_at": "2026-12-29T10:30:00+00:00",
    "is_expired": false,
    "active_domain": "example.com",
    "domain_changes_remaining": 2
  }
}
```

**Error Response:**

| Status | Message              |
|--------|----------------------|
| 404    | Product not found.   |
| 404    | Invalid license key. |

---

## Updates

### Check for Updates

Check if a newer version of a product is available.

**Endpoint:** `POST /api/update/check`

**Request Body:**

| Parameter         | Type   | Required | Description                                  |
|-------------------|--------|----------|----------------------------------------------|
| `license_key`     | string | Yes      | The license key for the product              |
| `domain`          | string | Yes      | The domain where the product is installed    |
| `product_slug`    | string | Yes      | The product slug identifier                  |
| `current_version` | string | Yes      | The currently installed version (e.g., "1.0.0") |

**Example Request:**

```bash
curl -X POST https://wplicence.jonakyds.com/api/update/check \
  -H "Content-Type: application/json" \
  -d '{
    "license_key": "XXXX-XXXX-XXXX-XXXX",
    "domain": "example.com",
    "product_slug": "my-plugin",
    "current_version": "1.0.0"
  }'
```

**Update Available Response (200):**

```json
{
  "success": true,
  "update_available": true,
  "current_version": "1.0.0",
  "latest_version": "1.2.0",
  "product": {
    "name": "My Plugin",
    "slug": "my-plugin"
  }
}
```

**No Update Available Response (200):**

```json
{
  "success": true,
  "update_available": false,
  "current_version": "1.2.0",
  "latest_version": "1.2.0",
  "product": {
    "name": "My Plugin",
    "slug": "my-plugin"
  }
}
```

**Error Responses:**

| Status | Message                                            |
|--------|----------------------------------------------------|
| 404    | Product not found.                                 |
| 403    | Invalid license key.                               |
| 403    | License is inactive.                               |
| 403    | License has expired. Please renew your license.    |
| 403    | License has been revoked.                          |
| 403    | License is not activated on this domain.           |

**Note:** Products that don't require a license can still check for updates without a valid license.

---

### Download Update

Get the download URL for the latest version.

**Endpoint:** `POST /api/update/download`

**Request Body:**

| Parameter      | Type   | Required | Description                               |
|----------------|--------|----------|-------------------------------------------|
| `license_key`  | string | Yes      | The license key for the product           |
| `domain`       | string | Yes      | The domain where the product is installed |
| `product_slug` | string | Yes      | The product slug identifier               |

**Example Request:**

```bash
curl -X POST https://wplicence.jonakyds.com/api/update/download \
  -H "Content-Type: application/json" \
  -d '{
    "license_key": "XXXX-XXXX-XXXX-XXXX",
    "domain": "example.com",
    "product_slug": "my-plugin"
  }'
```

**Success Response (200):**

```json
{
  "success": true,
  "download_url": "https://example.com/downloads/my-plugin-1.2.0.zip",
  "version": "1.2.0"
}
```

**Error Responses:**

| Status | Message                                  |
|--------|------------------------------------------|
| 404    | Product not found.                       |
| 403    | Valid license required for download.     |
| 403    | License is not activated on this domain. |
| 404    | Download not available.                  |

---

## Error Handling

### Validation Errors (422)

When request validation fails, the API returns a 422 status code with validation errors:

```json
{
  "message": "The license key field is required. (and 1 more error)",
  "errors": {
    "license_key": ["License key is required."],
    "product_slug": ["Product slug is required."]
  }
}
```

### Common Error Response Format

All error responses follow this structure:

```json
{
  "success": false,
  "message": "Description of the error"
}
```

### HTTP Status Codes

| Status Code | Description                                           |
|-------------|-------------------------------------------------------|
| 200         | Request successful                                    |
| 400         | Bad request (invalid domain, already deactivated, etc.) |
| 403         | Forbidden (revoked, expired, domain limit reached)    |
| 404         | Not found (invalid license key or product not found)  |
| 422         | Validation error (missing or invalid parameters)      |
| 500         | Server error                                          |

---

## License Object

The license object returned in responses contains the following fields:

| Field                       | Type    | Description                                  |
|-----------------------------|---------|----------------------------------------------|
| `status`                    | string  | License status: `active`, `inactive`, `revoked` |
| `product.name`              | string  | Product display name                         |
| `product.slug`              | string  | Product unique identifier                    |
| `product.version`           | string  | Current product version                      |
| `product.has_api_access`    | boolean | Whether the product has premium API access   |
| `activated_at`              | string  | ISO 8601 activation timestamp (nullable)     |
| `expires_at`                | string  | ISO 8601 expiration timestamp (nullable)     |
| `is_expired`                | boolean | Whether the license has expired              |
| `active_domain`             | string  | Currently activated domain (nullable)        |
| `domain_changes_remaining`  | integer | Number of domain changes still allowed       |

---

## Rate Limiting

The API currently does not enforce rate limiting. However, we recommend implementing reasonable request intervals in your integration to ensure optimal performance.

---

## Support

For API-related issues or questions, please contact support.
