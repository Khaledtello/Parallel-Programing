# High-Performance Ticket Booking Engine

A Laravel-based backend system designed to study and handle high-concurrency ticket booking scenarios, with a focus on concurrency control, transaction safety, background processing, caching, load distribution, and performance testing.

The project simulates use cases such as cinema seats, theaters, lectures, and event ticketing systems. Its primary focus is backend performance and reliability under concurrent load rather than building a complete administration platform.

## Project Goals

The system was designed around the following backend challenges:

- High-concurrency booking
- Preventing race conditions
- Safe resource and balance management
- Background processing
- Caching
- Load distribution
- Large-data processing
- Distributed locking
- Scalability-oriented design
- Performance and stability testing

## Main Booking Flow

A booking request follows this general flow:

1. The user selects an event.
2. The user selects a seat.
3. The system verifies seat availability.
4. A booking is created.
5. The user's balance is checked.
6. Ticket payment is processed in the background.
7. The booking is confirmed or cancelled.
8. The seat is released automatically when payment fails.
9. A notification job is dispatched for the booking result.

The system specifically simulates the case where multiple users attempt to reserve the same seat at nearly the same time.

## Core Features

### Ticket Booking

- Create a booking for an event seat.
- Prevent the same event seat from being booked more than once.
- Track booking and payment states.
- Release reserved seats when payment fails.

### Wallet and Payment Simulation

Each user has an internal balance.

The system:

- Checks the available balance.
- Deducts the ticket price.
- Rejects booking attempts when the balance is insufficient.
- Simulates a payment gateway.
- Handles simulated payment failure.
- Updates the booking state according to the payment result.

Payment is intentionally simulated as a backend workload rather than connected to a real external payment provider.

### Background Jobs and Queues

Payment processing and notifications were moved out of the initial HTTP request and implemented as background Jobs using Laravel Queues.

This separates the fast booking request from work that can be processed asynchronously by queue workers.

Benefits targeted by the design:

- Lower request latency
- Higher throughput
- Better server resource utilization

### Concurrency Control

One of the main problems investigated was the race condition that occurs when multiple requests try to reserve the same seat simultaneously.

A naive implementation based on:

```text
Check availability
        ↓
Create booking
```

can allow multiple requests to pass the check before the first booking is created.

The project addresses this using database transactions and row-level locking with `lockForUpdate()`.

The booking flow locks:

- The user's balance record
- The event-seat record

The event-seat state is then changed from:

```text
Available → Reserved
```

This ensures that concurrent requests cannot successfully reserve the same event seat through the same booking operation.

### ACID Transactions

The booking operation contains several dependent database changes:

- Create the booking
- Verify the user's balance
- Deduct the ticket price
- Update the event-seat state
- Continue the booking workflow

These operations are wrapped in a database transaction so that failures roll back the related changes instead of leaving the database in a partially updated state.

### Redis Caching

Redis caching was introduced for frequently requested data using a cache-aside approach.

The cache is used for queries such as:

- Individual event information
- Seats for an event
- Popular events

The intended result is to reduce repeated database queries and improve response performance for frequently requested data.

### Distributed Locks

When background workers are used, the same task may potentially be attempted by multiple workers or processes.

The project uses Redis-backed distributed locking around daily sales report generation so that the same report-generation operation is not executed concurrently.

### Large Dataset Processing

Daily sales reporting may process a large number of bookings.

Instead of loading all records into memory, the project processes bookings in smaller batches using Laravel's `chunkById()`:

```php
Booking::chunkById(1000, function ($bookings) {
    // Process batch
});
```

This reduces memory usage and makes large-data processing more stable.

### Multiple Queue Workers

The project was tested with multiple queue workers to study parallel background processing and resource utilization.

Multiple workers allow jobs to be processed concurrently and help distribute background workload rather than relying on a single worker process.

### Load Balancing

Nginx was used to distribute requests across multiple Laravel instances using a Round Robin strategy.

The setup was used to study:

- Request distribution
- Server load
- Throughput
- Stability under concurrent requests

Each Laravel instance was configured to listen on a different port.

## Domain Model

The core entities of the system are:

```text
User
  └── Balance

Venue
  └── Seats

Event
  └── Venue
  └── Event Seats

Event Seat
  └── Available / Reserved / Booked

Booking
  └── User
  └── Event Seat
  └── Booking Status
  └── Payment Status

Daily Sales Report
```

### Why `event_seats`?

A physical seat can be used by different events, while its booking state is specific to each event.

The `event_seats` relationship therefore allows the system to store an independent state for the same physical seat for different events:

```text
Available
Reserved
Booked
```

This model is also important for applying concurrency control to the exact event-seat record being booked.

## Performance Testing

### JMeter

Apache JMeter was used to simulate:

- Hundreds and thousands of users
- Concurrent requests
- High-load booking scenarios

The tests measured:

- Response time
- Throughput
- Error rate

The test plan included a mixture of operations rather than testing only one endpoint, including:

- Listing events
- Retrieving an event
- Listing popular events
- Retrieving available seats
- Performing bookings

### Grafana

Grafana was used to monitor server resource consumption during testing, including:

- CPU
- RAM

### Logging

Extended application logging was used to trace:

- Booking operations
- Payment processing
- Errors
- Concurrency conflicts
- Queue job processing

## Problems Investigated and Solutions

| Problem | Solution |
|---|---|
| Race condition when booking the same seat | Database transaction + `lockForUpdate()` |
| Duplicate balance deductions | User-row locking + transactional booking flow |
| Slow booking requests | Background payment and notification Jobs |
| High memory usage during large reports | `chunkById()` batch processing |
| Repeated database queries | Redis cache-aside caching |
| Duplicate report processing | Redis distributed lock |
| Single-worker bottleneck | Multiple queue workers |
| Excessive load on one application instance | Nginx Round Robin load balancing |
| Partial database updates after failures | ACID database transactions |

## Technology Stack

- **Backend:** Laravel / PHP
- **Database:** MySQL
- **Cache & Distributed Locking:** Redis
- **Background Processing:** Laravel Jobs / Queue Workers
- **Load Balancing:** Nginx
- **Load Testing:** Apache JMeter
- **Monitoring:** Grafana
- **Logging:** Laravel application logging

## What This Project Demonstrates

The main purpose of the project was not simply to implement CRUD endpoints. It was to explore how a Laravel backend behaves under concurrent load and how backend design decisions affect reliability and performance.

The project covers practical implementation of:

- Concurrency control
- Race-condition prevention
- Transactional workflows
- Asynchronous processing
- Queue workers
- Caching
- Distributed locking
- Batch processing
- Load balancing
- Load testing and monitoring

## Testing Focus

The project was evaluated by combining functional requests with concurrent load testing and monitoring.

The goal was to verify that the system could continue handling a large number of concurrent users while maintaining consistent booking and balance state.

## Project Scope

This project intentionally focuses on the backend booking engine and the technical problems around high-concurrency ticket booking.

It does **not** aim to provide a complete production ticketing platform or a full administration dashboard. Supporting entities such as users, venues, seats, and events exist primarily to support and test the booking workflow.

## References

The project was developed using the official documentation and references for:

- Laravel
- MySQL
- Nginx
- Apache JMeter

---

## Author

**Khaled Tello**

Information Technology Engineering Student — Damascus University

Backend / Laravel Developer
