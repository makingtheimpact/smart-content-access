# Smart Content Access

Cache-agnostic content gating via shortcodes. Supports MemberPress (optional), global plan IDs, roles, and user rules. Works with Elementor & Beaver Builder via shortcodes.

**Contributors:** makingtheimpact  
**Requires at least:** 5.0  
**Tested up to:** 6.4  
**Stable tag:** 1.0.0  
**Requires PHP:** 7.4  
**License:** GPL-2.0+  
**License URI:** https://www.gnu.org/licenses/gpl-2.0.html

## Description

Smart Content Access provides a flexible, cache-friendly content gating system for WordPress. Control access to your content using shortcodes that work seamlessly with page builders like Elementor and Beaver Builder.

### Key Features

* **Cache-Agnostic**: Automatically sends cache-busting headers when content uses SCA shortcodes
* **Multiple Access Methods**: Control access by roles, specific users, or MemberPress subscriptions
* **Global Settings**: Set default access rules and global MemberPress product IDs
* **Flexible Logic**: Use "any" (OR) or "all" (AND) logic for multiple requirements
* **Page Builder Compatible**: Integrates with Elementor and Beaver Builder widgets
* **Simple Shortcodes**: Easy-to-use shortcodes for members, guests, and conditional rendering
* **Menu Visibility Control**: Control WordPress menu item visibility based on user status, roles, or MemberPress subscriptions

### Access Control Options

* **MemberPress Integration** (optional): Check for active subscriptions to specific products
* **User Roles**: Restrict access based on WordPress user roles
* **Specific Users**: Grant access to individual users by ID
* **Logged-in Users**: Control access for authenticated users
* **Global Settings**: Set site-wide default behaviors

### Cache-Friendly

The plugin automatically detects when content uses SCA shortcodes and sends appropriate cache headers to ensure dynamic content is always served correctly.

## Installation

1. Upload the plugin files to the `/wp-content/plugins/smart-content-access` directory, or install the plugin through the WordPress plugins screen directly
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Configure global settings at **Settings → Smart Content Access**

## Frequently Asked Questions

### Does this work with caching plugins?

Yes! The plugin is cache-agnostic and automatically sends appropriate cache-busting headers when content uses SCA shortcodes.

### Is MemberPress required?

No. The plugin works without MemberPress. MemberPress integration is optional and automatically detected if available.

### Does it work with page builders?

Yes, Smart Content Access integrates with:
* **Elementor** - Native "SCA Content Gate" widget with visual controls
* **Beaver Builder** - Native "SCA Content Gate" module with visual controls

When Elementor or Beaver Builder is active, dedicated widgets/modules appear in the page builder interface. These widgets render content dynamically (server-side) to prevent caching issues and provide a user-friendly visual interface for setting access rules.

You can also use the shortcodes via the Shortcode/Text widgets in both page builders.

### Can I use multiple access methods together?

Yes. You can combine role checks, user checks, and MemberPress product checks. Use the `require` attribute to specify "any" (OR) or "all" (AND) logic.

## Screenshots

1. Settings page with global configuration options
2. Shortcode examples and usage guide

## Configuration

Navigate to **Settings → Smart Content Access** to configure global settings:

* **Global MemberPress Product IDs**: Set default product IDs to check (comma-separated)
* **Global Require**: Choose "any" or "all" when checking multiple products
* **Default Behavior**: Choose what happens when no rules are specified (logged-in only or open to all)

## Usage

### Basic Shortcodes

#### Content Gating
```
[sca_gate mp_ids="123,456" require="any" roles="subscriber" users="9050"]Hidden content for authorized users[/sca_gate]
```

#### Render Specific Content
```
[sca_render source="post_content"]
```

Render featured image:
```
[sca_render source="featured_image" image_size="large"]
```

Render post excerpt (great for previews):
```
[sca_render source="post_excerpt"]
```
*Note: When generating excerpts automatically, all non-text content (images, embeds, videos, shortcodes, URLs) is stripped to ensure clean text-only previews.*

Render a shortcode conditionally (brackets optional - will be added automatically):
```
[sca_render source="shortcode" short='your_custom_shortcode']
```

#### Member Only / Guest Only
```
[sca_member]Content for members only[/sca_member]
[sca_guest]Content for guests only[/sca_guest]
```

### Advanced Examples

Check for multiple MemberPress products with "all" logic:
```
[sca_gate mp_ids="123,456" require="all"]Content for users with both products[/sca_gate]
```

Invert access (show content to unauthorized users):
```
[sca_gate roles="administrator" invert="1"]Content NOT for administrators[/sca_gate]
```

### Using Page Builder Widgets

#### Elementor
1. Open any Elementor page/template
2. Search for "SCA Content Gate" in the widget panel
3. Drag and drop the widget onto your page
4. Configure access control settings:
   - **MemberPress Product IDs** (comma-separated) - Leave blank to use global settings or show to all
   - **Required User Roles** (comma-separated slugs like `subscriber, editor`)
   - **Specific User IDs** (comma-separated IDs like `123, 456`)
   - **Match Logic**: ANY (OR) or ALL (AND) for multiple criteria
   - **Invert Result**: Show to unauthorized users instead
5. Add your content in the "Authorized Content" field (shortcodes supported!)
6. Optionally add "Unauthorized Content" to show to users without access

**Default Behavior:** If all fields are left blank, the widget uses the global settings from **Settings → Smart Content Access**. You can paste HTML, shortcodes, and formatted text in the content fields.

#### Beaver Builder
1. Open any Beaver Builder page
2. Search for "SCA Content Gate" in the module panel
3. Add the module to your layout
4. Configure access control in the "Access Control" tab:
   - Leave fields blank to inherit global settings
   - Use MemberPress IDs, roles, or user IDs as needed
5. Add your content in the "Content" tab (shortcodes supported!)
6. Save and publish

**Note:** The widgets render content dynamically (server-side) to ensure each user sees the correct content. This prevents caching issues common with page builders.

## Shortcode Attributes

### `sca_gate`
* `mp_ids` - MemberPress product IDs (comma-separated)
* `require` - "any" or "all" logic for multiple requirements
* `roles` - WordPress user roles (comma-separated)
* `users` - User IDs (comma-separated)
* `invert` - "1" to invert access (show to unauthorized users)

### `sca_render`
All attributes from `sca_gate`, plus:
* `source` - Content source: "post_content", "post_excerpt", "featured_image", or "shortcode"
* `post_id` - Post ID to render from (default: "current")
* `image_size` - WordPress image size for featured images
* `short` - Shortcode to execute when source="shortcode". Provide without brackets (e.g., `short='your_shortcode'`), the plugin will add them automatically

### `sca_member` / `sca_guest`
Same as `sca_gate`. `sca_member` shows content to authorized users, `sca_guest` shows to unauthorized users.

## Menu Visibility

Control which menu items appear to which users in your WordPress navigation menus.

### Accessing Menu Visibility Settings

1. Go to **Appearance → Menus** (or **Appearance → Menus** depending on your theme)
2. Expand any menu item
3. Find the **Smart Content Access** section at the bottom

### Menu Visibility Options

* **Visible to everyone** (default): Menu item shows for all users
* **Logged-in users only**: Menu item only shows to logged-in users
* **Logged-out visitors only**: Menu item only shows to visitors who are not logged in
* **MemberPress Product IDs**: Comma-separated product IDs - menu item shows only to users with those subscriptions
* **Required user roles**: Comma-separated role slugs - menu item shows only to users with those roles
* **Specific user IDs**: Comma-separated user IDs - menu item shows only to those specific users
* **Matching logic**: Choose "any" (OR) or "all" (AND) for multiple criteria
* **Invert result**: Checkbox to show item when conditions fail (opposite logic)

### Example

To show a "Members Area" link only to logged-in subscribers:
- Set **Quick rule** to "Logged-in users only"
- Or use **MemberPress Product IDs** with your product IDs
- Or use **Required user roles** with "subscriber"

## Requirements

* WordPress 5.0 or higher
* PHP 7.4 or higher
* Optional: MemberPress for subscription-based access control
* Optional: Elementor or Beaver Builder for visual editing

## Changelog

### 1.0.0
* Initial release
* Content gating via shortcodes
* MemberPress integration
* Role and user-based access control
* Cache-busting headers
* Elementor and Beaver Builder integration
* Global settings configuration

## Developer API

### Filter Hooks

#### `sca_is_authorized`

Filter the authorization result before it's returned.

```php
add_filter( 'sca_is_authorized', function( $allowed, $args, $checks ) {
    // $allowed: Current authorization result (bool)
    // $args: The authorization arguments (array)
    // $checks: Array of individual check results (array)
    
    // Example: Override for specific users
    if ( $args['user_id'] === 123 ) {
        return true;
    }
    
    return $allowed;
}, 10, 3 );
```

### Debug Logging

When `WP_DEBUG` and `WP_DEBUG_LOG` are enabled, the plugin logs authorization checks to `wp-content/debug.log`:

```
[SCA DEBUG] Authorization check
- User ID
- Arguments (mp_ids, roles, users, etc.)
- Individual check results
- Final authorization result
```

This helps troubleshoot access issues without modifying code.

### Helper Functions

```php
// Get plugin options
$options = sca_get_options();

// Convert CSV to integers
$ids = sca_csv_to_ints( '123, 456, 789' ); // Returns [123, 456, 789]

// Convert CSV to sanitized strings
$roles = sca_csv_to_strings( 'subscriber, editor' ); // Returns ['subscriber', 'editor']

// Check if content has SCA shortcodes
$has_sca = sca_content_has_shortcodes( $content );

// Debug logging
sca_debug_log( 'Message', 'info' );
sca_debug_log( $data_array, 'debug' );
```

## Support

For support, feature requests, or bug reports, please contact **Making The Impact LLC**.

## Credits

**Developer:** Making The Impact LLC  
**License:** GPL-2.0+

## Credits

Developed by Making The Impact LLC.

