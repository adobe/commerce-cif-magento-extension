# CIF Magento's extensions

Magento Extensions that are used for [CIF](https://github.com/adobe/commerce-cif-magento).

## AggregatedServices
   
 **AggregatedServices** module provides additional web API endpoints that enables [CIF](https://github.com/adobe/commerce-cif-magento) to query for data using fewer number of requests.
 One example is the `guest-aggregated-carts` endpoint that return the entire cart information including cart totals, product and product attributes definitions.  
 
Current extension exposes product data to anonymous users. In the default Magento installation product data is not visible to anonymous users, however anonymous access can be enabled via the admin panel.

### Extension Local Installation

1. Copy it under `app/code/Magento` directory
2. Clear cache
3. Enable module https://devdocs.magento.com/guides/v2.0/install-gde/install/cli/install-cli-subcommands-enable.html
4. Make requests to `GET /V1/guest-aggregated-carts/:cartId` without token as a guest
5. Pass `productAttributesSearchCriteria` to fetch attributes in the same request

### Magento aggregated guest cart sample

`http://<host>/rest/V1/guest-aggregated-carts/<cartId>?productAttributesSearchCriteria[filter_groups][0][filters][0][field]=attribute_code&productAttributesSearchCriteria[filter_groups][0][filters][0][value]=color&productAttributesSearchCriteria[filter_groups][0][filters][1][field]=attribute_code&productAttributesSearchCriteria[filter_groups][0][filters][1][value]=size`

### Magento aggregated customer cart sample

`http://<host>/rest/V1/customer-aggregated-carts/mine?productAttributesSearchCriteria[filter_groups][0][filters][0][field]=attribute_code&productAttributesSearchCriteria[filter_groups][0][filters][0][value]=color&productAttributesSearchCriteria[filter_groups][0][filters][1][field]=attribute_code&productAttributesSearchCriteria[filter_groups][0][filters][1][value]=size`

Set `Authorization` header to `Bearer <token_value>`, where token value is retrieved using the following request:

`POST {{magento_base_url}}/rest/V1/integration/customer/token`

```json
{
	"username": "customer@example.com",
	"password": "123123qQ"
}
```
 ## Contributing
 
 Contributions are welcomed! Read the [Contributing Guide](CONTRIBUTING.md) for more information.
 
 ## Licensing
 
 This project is licensed under the Apache V2 License. See [LICENSE](LICENSE) for more information.

