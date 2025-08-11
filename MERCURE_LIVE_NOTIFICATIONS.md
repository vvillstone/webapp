# Mercure Live Notifications System

This document explains the Mercure live notifications system implemented for Timesheet and Site entities in the Symfony 6 modular application.

## Overview

The system provides real-time notifications when Timesheet and Site entities are created, updated, or deleted. It uses Symfony Mercure Hub to publish events and EventSource API to subscribe to these events in the browser.

## Architecture

### Components

1. **Doctrine Event Listeners**: Automatically detect entity changes
2. **Mercure Hub**: Publishes events to subscribers
3. **Twig Templates**: Display live notifications using EventSource
4. **Test Controllers**: Manual event triggering for testing

### Event Flow

```
Entity Change → Doctrine Event Listener → Mercure Hub → EventSource → Browser Display
```

## Implementation Details

### 1. Doctrine Event Listeners

#### TimesheetEventListener
- **Location**: `src/Modules/Business/EventListener/TimesheetEventListener.php`
- **Events**: `postPersist`, `postUpdate`, `postRemove`
- **Topics**: `timesheet.created`, `timesheet.updated`, `timesheet.deleted`

#### SiteEventListener
- **Location**: `src/Modules/Business/EventListener/SiteEventListener.php`
- **Events**: `postPersist`, `postUpdate`, `postRemove`
- **Topics**: `site.created`, `site.updated`, `site.deleted`

### 2. Event Data Structure

Each event contains:
```json
{
    "id": 123,
    "type": "timesheet|site",
    "timestamp": "2024-01-15T10:30:00+00:00",
    "data": {
        "action": "created|updated|deleted",
        // Entity-specific data
    }
}
```

#### Timesheet Event Data
```json
{
    "action": "created",
    "employee": "John Doe",
    "site": "Test Site",
    "client": "Test Client",
    "date": "2024-01-15",
    "hours": 8.0,
    "status": "draft"
}
```

#### Site Event Data
```json
{
    "action": "created",
    "name": "Test Site",
    "client": "Test Client",
    "address": "123 Test Street, Test City, 12345",
    "status": "active"
}
```

### 3. Twig Templates

#### Dashboard Template
- **Location**: `templates/Business/live_notifications/dashboard.html.twig`
- **Features**: 
  - All events (timesheet + site)
  - Connection status
  - Event filtering
  - Statistics
  - Test controls

#### Timesheet Template
- **Location**: `templates/Business/live_notifications/timesheets.html.twig`
- **Features**: Timesheet-specific events only

#### Site Template
- **Location**: `templates/Business/live_notifications/sites.html.twig`
- **Features**: Site-specific events only

### 4. JavaScript EventSource Implementation

```javascript
// Initialize EventSource connection
function initializeEventSource() {
    const hubUrl = '{{ mercure_hub_url }}';
    const topics = ['timesheet.created', 'timesheet.updated', 'timesheet.deleted'];
    
    const url = new URL(hubUrl);
    topics.forEach(topic => {
        url.searchParams.append('topic', topic);
    });
    
    eventSource = new EventSource(url.toString());
    
    eventSource.onmessage = function(event) {
        const data = JSON.parse(event.data);
        handleNotification(data);
    };
}
```

## Usage

### 1. Access Live Notifications

#### Dashboard (All Events)
```
http://localhost/live-notifications
```

#### Timesheet Events Only
```
http://localhost/live-notifications/timesheets
```

#### Site Events Only
```
http://localhost/live-notifications/sites
```

### 2. Test Events

#### Manual Event Testing
```
POST /mercure-test/timesheet-created
POST /mercure-test/timesheet-updated
POST /mercure-test/site-created
POST /mercure-test/site-updated
POST /mercure-test/all-events
```

#### Using cURL
```bash
# Test timesheet created event
curl -X POST http://localhost/mercure-test/timesheet-created

# Test site created event
curl -X POST http://localhost/mercure-test/site-created

# Test all events
curl -X POST http://localhost/mercure-test/all-events
```

### 3. Real Entity Operations

To see real events, perform CRUD operations on Timesheet and Site entities:

#### Via API Platform
```
POST /api/timesheets
PUT /api/timesheets/{id}
DELETE /api/timesheets/{id}

POST /api/sites
PUT /api/sites/{id}
DELETE /api/sites/{id}
```

#### Via Twig Interfaces
- Navigate to entity management pages
- Create, update, or delete entities
- Watch live notifications appear

## Configuration

### 1. Mercure Configuration

**File**: `config/packages/mercure.yaml`
```yaml
mercure:
    hubs:
        default:
            url: '%env(MERCURE_URL)%'
            public_url: '%env(MERCURE_PUBLIC_URL)%'
            jwt:
                secret: '%env(MERCURE_JWT_SECRET)%'
                publish: '*'
                subscribe: '*'
```

### 2. Environment Variables

**File**: `.env`
```env
MERCURE_URL=http://mercure:80/.well-known/mercure
MERCURE_PUBLIC_URL=http://localhost:3000/.well-known/mercure
MERCURE_JWT_SECRET=!ChangeThisMercureHubJWTSecretKey!
```

### 3. Services Configuration

**File**: `src/Modules/Business/Resources/config/services.yaml`
```yaml
services:
    Modules\Business\EventListener\TimesheetEventListener:
        tags:
            - { name: 'doctrine.event_subscriber' }
    
    Modules\Business\EventListener\SiteEventListener:
        tags:
            - { name: 'doctrine.event_subscriber' }
```

## Features

### 1. Real-time Updates
- Instant notification when entities change
- No page refresh required
- Automatic reconnection on connection loss

### 2. Visual Indicators
- Color-coded notifications by action type
- Status badges for entity states
- Connection status indicator
- Animation effects for new notifications

### 3. Filtering and Controls
- Filter by entity type (timesheet/site)
- Clear all notifications
- Reconnect manually
- Test event triggers

### 4. Statistics
- Event counters by type
- Total event count
- Real-time updates

## Troubleshooting

### 1. No Events Received

**Check Mercure Hub:**
```bash
# Verify Mercure is running
docker-compose ps mercure

# Check Mercure logs
docker-compose logs mercure
```

**Check EventSource Connection:**
- Open browser developer tools
- Look for EventSource errors in console
- Verify hub URL is correct

### 2. Events Not Published

**Check Event Listeners:**
```bash
# Verify services are registered
php bin/console debug:container | grep EventListener
```

**Check Doctrine Events:**
```bash
# Verify entity changes are detected
php bin/console debug:event-dispatcher
```

### 3. Template Issues

**Check Template Paths:**
- Verify templates exist in correct locations
- Check template inheritance
- Ensure base template is available

### 4. CORS Issues

**Check Nginx Configuration:**
```nginx
# Ensure CORS headers are set
add_header Access-Control-Allow-Origin *;
add_header Access-Control-Allow-Methods "GET, POST, OPTIONS";
add_header Access-Control-Allow-Headers "Content-Type";
```

## Security Considerations

### 1. JWT Configuration
- Use strong, unique JWT secrets
- Rotate secrets regularly
- Limit publish/subscribe permissions as needed

### 2. Topic Security
- Consider implementing topic-based authorization
- Validate event data before publishing
- Sanitize data in templates

### 3. Rate Limiting
- Implement rate limiting for test endpoints
- Monitor event frequency
- Set appropriate limits

## Performance Optimization

### 1. Event Filtering
- Subscribe only to needed topics
- Implement client-side filtering
- Use efficient data structures

### 2. Connection Management
- Implement exponential backoff for reconnections
- Limit concurrent connections
- Clean up EventSource on page unload

### 3. Memory Management
- Limit notification history
- Implement garbage collection
- Monitor memory usage

## Extending the System

### 1. Adding New Entities

1. Create entity event listener
2. Register in services configuration
3. Add topics to templates
4. Update JavaScript handlers

### 2. Custom Event Types

1. Define event structure
2. Update event listeners
3. Modify templates
4. Add JavaScript handlers

### 3. Advanced Features

- User-specific notifications
- Notification persistence
- Email integration
- Mobile push notifications

## Monitoring and Logging

### 1. Event Logging
```php
// Add logging to event listeners
$this->logger->info('Timesheet event published', [
    'action' => $action,
    'entity_id' => $timesheet->getId()
]);
```

### 2. Performance Monitoring
- Track event frequency
- Monitor connection stability
- Measure response times

### 3. Error Tracking
- Log EventSource errors
- Track failed publications
- Monitor reconnection attempts

## Conclusion

The Mercure live notifications system provides a robust, real-time notification system for entity changes. It's designed to be extensible, performant, and easy to use. The modular architecture allows for easy addition of new entity types and custom event handling.
