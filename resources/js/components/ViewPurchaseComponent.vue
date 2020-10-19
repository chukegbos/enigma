<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container">
            <div class="row mt-2">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">
                                Purchase Details
                            </div>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover">
                                <tr>
                                    <th>Purchase ID</th>
                                    <th>Products</th>
                                    <th>Quantity</th>
                                    <th>Price (<span v-html="nairaSign"></span>)</th>
                                    <th>Date of Purchase</th>
                                </tr>

                                <tr v-for="purchase in groupPurchases" :key="purchase.id">
                                    <td>{{ purchase.purchase_id }}</td>
                                    <td>{{ purchase.product_name }}</td>
                                    <td>{{ purchase.quantity }}</td>
                                    <td>{{ purchase.total_price }}</td>
                                    <td>{{ purchase.purchase_date }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="card-footer">
                            <pagination
                                :data="purchases"
                                @pagination-change-page="getResults"
                            ></pagination>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </b-overlay>
</template>

<script>
export default {
    created() {
        this.viewPurchase();
        this.getProduct();
        this.getUser();
    },

    data() {
        return {
            is_busy: false,
            nairaSign: "&#x20A6;",
            editMode: false,
            model: {},
            purchases: {},
            groupPurchases: "",
          
            singleSupplier: "",
            singleProduct: "",
            user: "",
            form: new Form({
                id: "",
                supplier_name: "",
                contact_person: "",
                email: "",
                phone: "",
                address: "",
                product_name: null,
                account_name: "",
                product_account: ""
            }),

            products: {},
            errors: []
        };
    },

    methods: {
        getUser() {
            axios.get("/api/user")
            .then(({ data }) => {
                this.user = data.user;
            });
        },

        getResults(page = 1) {
            axios.get("/api/purchases?page=" + page)
            .then(response => {
                this.purchases = response.data;
            });
        },

        viewPurchase() {
            axios.get("/api/purchases/" + this.$route.params.id)
            .then(({ data }) => {
                this.groupPurchases = data;
            });
        },

        getProduct() {
            axios.get("/api/inventory")
            .then(({ data }) => {
                this.products = data.data;
            });
        }
    }
};
</script>
