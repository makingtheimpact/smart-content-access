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
* Elementor
* Beaver Builder

Widgets are available in the page builder interface when these plugins are active.

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

Render a shortcode conditionally:
```
[sca_render source="shortcode" short='[your_custom_shortcode]']
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

## Shortcode Attributes

### `sca_gate`
* `mp_ids` - MemberPress product IDs (comma-separated)
* `require` - "any" or "all" logic for multiple requirements
* `roles` - WordPress user roles (comma-separated)
* `users` - User IDs (comma-separated)
* `invert` - "1" to invert access (show to unauthorized users)

### `sca_render`
All attributes from `sca_gate`, plus:
* `source` - Content source: "post_content", "featured_image", or "shortcode"
* `post_id` - Post ID to render from (default: "current")
* `image_size` - WordPress image size for featured images
* `short` - Shortcode to execute when source="shortcode"

### `sca_member` / `sca_guest`
Same as `sca_gate`. `sca_member` shows content to authorized users, `sca_guest` shows to unauthorized users.

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

## Support

For support, feature requests, or bug reports, please contact **Making The Impact LLC**.

## Credits

**Developer:** Making The Impact LLC  
**License:** GPL-2.0+

## Credits

Developed by Making The Impact LLC.

