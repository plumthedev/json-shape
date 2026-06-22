# Usage guide

This guide is written as a series of short chapters. Each one answers a single
question and builds on the one before it, so the fastest way through is simply
top to bottom. By the end you'll have a typed JSON column working on a real
Eloquent model, and you'll know every part of the package.

If you only have a minute, read [Chapter 1](/usage/defining-a-shape) and
[Chapter 4](/usage/eloquent-casting) — that's the whole loop: define a shape,
cast it on a model.

## The chapters

1. **[Define a shape](/usage/defining-a-shape)** — How do I describe the structure of a JSON column?
2. **[Read values](/usage/reading-values)** — How do I get typed values out of a shape?
3. **[Write values](/usage/writing-values)** — How do I change values and keep them typed?
4. **[Cast it on a model](/usage/eloquent-casting)** — How do I use a shape as an Eloquent attribute?
5. **[Create & combine shapes](/usage/creating-shapes)** — How do I build shapes by hand, in tests and jobs?
6. **[Type safety in depth](/usage/type-safety)** — What does static analysis actually catch for me?
7. **[Helpers, macros & errors](/usage/helpers)** — What else does a shape give me out of the box?

## The running example

Every chapter uses one example: a `TraceShape` that models a structured log
trace stored in a JSON column. It's deliberately realistic — it has required
fields, an optional one, a nullable one, an enum, and a nested object — so each
feature has somewhere to land. You'll define it in
[Chapter 1](/usage/defining-a-shape) and reuse it everywhere after.

Ready? **[Start with Chapter 1 →](/usage/defining-a-shape)**
