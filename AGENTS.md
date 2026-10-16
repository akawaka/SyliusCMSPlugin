See @CLAUDE.md

# Project rules

## Constants and states

- Never use class constants (`public const STATUS_DRAFT = '...'`) to represent a fixed set of values (statuses, types, transitions, modes…). Use a PHP backed **Enum** instead, in `src/Enum/` (e.g. `PageStatus`, `PageTransition`).
- Doctrine columns holding such a value are mapped with `enumType:` and typed with the enum in the entity (never `string`).
- Forms use `EnumType` (or choices built from the enum cases); labels come from a `label()` method on the enum returning a translation key.
- In YAML, reference enum cases with `!php/enum Fqcn::Case->value`, never with hardcoded strings.

## Workflow / state machine

- Any lifecycle status (e.g. page draft/published) is driven by the **Symfony Workflow** component, configured as a `state_machine` under `framework.workflows` in `config/app/workflow.yaml`.
- Places and transition names come from enums (`PageStatus`, `PageTransition`); the marking store is `method` on the `status` property.
- The status is never edited through a free form field: it changes only through transitions (grid `apply_transition` actions and routes using `applyStateMachineTransitionAction`, or `WorkflowInterface::apply()` in code). Guard with `$workflow->can()`.
