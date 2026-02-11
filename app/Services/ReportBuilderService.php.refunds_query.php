    private function buildRefundsDetailedQuery($filters, $columns)
    {
         // Standard Search Query
         $searchQuery = $this->buildSearchQuery($filters, ['created_at', 'updated_at', 'status', 'tag']);
         
         // Force status:any if not present
         if (strpos($searchQuery, 'status:') === false) {
             if (empty($searchQuery)) {
                 $searchQuery = "status:any";
             } else {
                 $searchQuery .= " AND status:any";
             }
         }
         
         // Always filter only orders with refunds?
         // "financial_status:refunded OR financial_status:partially_refunded"
         // But user might filter by date.
         // If we add this filter, we might miss orders with 0 refunds but user wants to see them?
         // No, it's a "Refunds" report.
         // We can append " AND (financial_status:refunded OR financial_status:partially_refunded)"
         // But "OR" prededence is tricky in Shopify search syntax.
         // Better to fetch all (status:any) and filter in PHP by totalRefunded > 0.
         // This is safer.

         return "{ orders(first: 250, sortKey: CREATED_AT, reverse: true, query: \"{$searchQuery}\") { edges { node { id name createdAt displayFinancialStatus displayFulfillmentStatus app { name } customer { firstName lastName displayName email } note refunds { note createdAt } totalRefundedSet { shopMoney { amount } } totalTaxSet { shopMoney { amount } } totalShippingPriceSet { shopMoney { amount } } totalPriceSet { shopMoney { amount } } totalDiscountsSet { shopMoney { amount } } subtotalPriceSet { shopMoney { amount } } lineItems(first: 250) { edges { node { id title quantity variant { title sku price } originalUnitPriceSet { shopMoney { amount } } discountedUnitPriceSet { shopMoney { amount } } } } } } } } }";
    }
