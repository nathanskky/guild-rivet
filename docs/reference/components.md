# Components

> For PHP developers looking up a guild/rivet component's tag name, body, parent or exceptions.

guild/rivet has 49 components. The Twig names are below; in Latte, write the same name in `camelCase`,
such as `rvtCardBody`. A component with a body is a paired tag, and one without is a single tag.

| Component | Body | Notes |
|---|---|---|
| `rvt_accordion` | Yes | container for `rvt_accordion_panel` |
| `rvt_accordion_panel` | Yes | requires `rvt_accordion` |
| `rvt_alert` | Yes | dismissible page-level message |
| `rvt_avatar` | No | image or initials, max 2 |
| `rvt_badge` | No | |
| `rvt_breadcrumbs` | No | `items` array; last item is the current page |
| `rvt_button` | Yes | `text` for the short form, or a body for icon + label |
| `rvt_button_group` | Yes | |
| `rvt_card` | Yes | with `rvt_card_image`, `rvt_card_body` |
| `rvt_card_body` | Yes | requires `rvt_card` |
| `rvt_card_image` | No | requires `rvt_card` |
| `rvt_checkbox` | No | valid alone or inside `rvt_field_group` |
| `rvt_column` | Yes | width 1–12, scoped to `-md` and up |
| `rvt_container` | Yes | |
| `rvt_dialog` | Yes | renders its own trigger; with `rvt_dialog_body`, `rvt_dialog_controls` |
| `rvt_dialog_body` | Yes | requires `rvt_dialog`; supplies the described-by target |
| `rvt_dialog_controls` | Yes | requires `rvt_dialog` |
| `rvt_disclosure` | Yes | |
| `rvt_dropdown` | Yes | menu items are bare links or buttons |
| `rvt_field_group` | Yes | fieldset + legend for checkboxes and radios |
| `rvt_file_input` | No | |
| `rvt_footer` | No | IU-required links and copyright built in |
| `rvt_form_field` | Yes | label, control, helper text, errors |
| `rvt_header` | No | `items` nav tree; children become a dropdown |
| `rvt_icon` | No | needs the `rivet-icons` package |
| `rvt_inline_alert` | Yes | validation message; one glyph per severity |
| `rvt_input_group` | Yes | with `rvt_input_group_addon` |
| `rvt_input_group_addon` | Yes | requires `rvt_input_group` |
| `rvt_list` | Yes | |
| `rvt_loader` | No | |
| `rvt_page` | Yes | assembles the whole document; needs `PageDefaults` |
| `rvt_page_breadcrumbs` | Yes | slot; lands in the heading band |
| `rvt_page_scripts` | Yes | slot; lands before `</body>`, after Rivet's script |
| `rvt_page_sidebar` | Yes | slot; requires the `sidebar` or `anchored_sidebar` layout |
| `rvt_page_styles` | Yes | slot; lands in `<head>`, after Rivet's stylesheet |
| `rvt_pagination` | No | unavailable arrows drop the link entirely |
| `rvt_radio` | No | **requires** `rvt_field_group` |
| `rvt_row` | Yes | |
| `rvt_segmented_buttons` | Yes | `label` required |
| `rvt_select` | No | `options` array; requires `rvt_form_field` |
| `rvt_sidenav` | No | recursive `items`, max 4 levels |
| `rvt_step_indicator` | No | |
| `rvt_subnav` | No | |
| `rvt_switch` | No | `label` required |
| `rvt_tab` | Yes | requires `rvt_tabs`; renders through its parent |
| `rvt_table` | Yes | caption always emitted |
| `rvt_tabs` | Yes | `label` required |
| `rvt_text_input` | No | requires `rvt_form_field` |
| `rvt_textarea` | No | requires `rvt_form_field` |

To list every component's parameters, run `composer components` in a checkout of guild/rivet. Each
component class also documents its parameters in its docblock.

## Exceptions

| Exception | Thrown when |
|---|---|
| `ComponentContextException` | A component is used outside the parent it needs, such as `rvt_radio` outside `rvt_field_group`. |
| `InvalidArgumentException` | A value can't be rendered, such as an unknown component, a button with no accessible name, a column width outside 1–12, or a combination Rivet has no class for. |
| `ConfigurationException` | `rvt_page` renders without a `PageDefaults`. |

All three are in `Guild\Rivet\Exception` and implement `RivetException`, so you can catch any of them
with that interface. Both engines report the template file and line where a component threw.
