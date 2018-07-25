
# Magento Extension Local Installation

1. Copy it under `app/code/Magento` directory
2. Clear cache
3. Enable module https://devdocs.magento.com/guides/v2.0/install-gde/install/cli/install-cli-subcommands-enable.html
4. Make requests to `GET /V1/guest-aggregated-carts/:cartId` with admin token
5. Pass `productAttributesSearchCriteria` to fetch attributes in the same request, until you give me the rules to identify which attributes to fetch (edited)

# Magento aggregated cart sample

`http://<host>/rest/V1/guest-aggregated-carts/<cartId>?productAttributesSearchCriteria[filter_groups][0][filters][0][field]=attribute_code&productAttributesSearchCriteria[filter_groups][0][filters][0][value]=color&productAttributesSearchCriteria[filter_groups][0][filters][1][field]=attribute_code&productAttributesSearchCriteria[filter_groups][0][filters][1][value]=size`
