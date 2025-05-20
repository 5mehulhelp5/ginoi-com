

================================================================
GraphQL
================================================================

{
  customer {
    orders(
      pageSize: 100
    ) {
      items {
        id
        order_date
        total {
          grand_total {
            value
            currency
          }
        }
        status
        shipments{
            tracking{
                title
                carrier
                number
                trackingurl
            }
         }
      }
    }
  }
}


================================================================
