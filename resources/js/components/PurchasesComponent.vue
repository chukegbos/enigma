<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container">
            <div class="row">
                <div class="col-md-6 mb-4">
                    <b-button variant="outline-primary" size="sm" v-b-modal.filter-modal><i class="fa fa-filter"></i> Filter</b-button>
                    <b-modal id="filter-modal" ref="filter" title="Filter" hide-footer>
                        <b-form @submit.stop.prevent="onFilterSubmit">

                            <div class="row">
                                <div class="col-lg-6">
                                    <b-form-group label="Start Date:" label-for="Start Date">
                                        <b-form-datepicker v-model="filterForm.start_date" placeholder="Start date"
                                            :date-format-options="{ year: 'numeric', month: 'short', day: '2-digit', weekday: 'short' }">
                                        </b-form-datepicker>
                                    </b-form-group>
                                </div>

                                <div class="col-lg-6">
                                    <b-form-group label="End Date:" label-for="End Date">
                                        <b-form-datepicker v-model="filterForm.end_date" placeholder="End date"
                                            :date-format-options="{ year: 'numeric', month: 'short', day: '2-digit', weekday: 'short' }">
                                        </b-form-datepicker>
                                    </b-form-group>
                                </div>
                            </div>

                            <b-form-group label="Purchase Code:">
                                <b-form-input id="name" v-model="filterForm.name" type="text"></b-form-input>
                            </b-form-group>

                            <b-button type="submit" variant="primary">Filter</b-button>
                        </b-form>
                    </b-modal>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">
                                Purchases
                            </div>
                            <div class="card-tools" v-if="user.role == 3">
                                <router-link to="/admin/purchase/create">
                                    <button class="btn btn-success">
                                        Add New
                                        <i
                                            class="fa fa-purchase-plus fa-fw"
                                        ></i>
                                    </button>
                                </router-link>
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
                                    <th>Modify</th>
                                </tr>

                                <tr
                                    v-for="purchase in purchases.data"
                                    :key="purchase.purchase_id"
                                >
                                    <td>{{ purchase.purchase_id }}</td>
                                    <td>{{ purchase.product_name }}</td>
                                    <td>{{ purchase.quantity }}</td>
                                    <td>{{ purchase.total_price }}</td>
                                    <td>{{ purchase.purchase_date }}</td>
                                    <td>
                                        <!--<router-link :to="'/admin/purchases/' + purchase.purchase_id">
                                            <i class="fa fa-edit text-blue"></i>
                                        </router-link>-->
                                      
                                        <a href="#" @click="deletePurchase(purchase.id)">
                                            <i class="fa fa-trash text-red"></i>
                                        </a>
                                    </td>
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
    import moment from 'moment'
    export default {
        created() {
            this.loadPurchase();
            this.getProduct();
            this.getUser();
        },

        data() {
            return {
                filterForm: {
                    start_date: moment().subtract(30, 'days').format("YYYY-MM-DD"),
                    end_date: moment().format("YYYY-MM-DD"),
                    name: '',
                },
                is_busy: false,
                nairaSign: "&#x20A6;",
                editMode: false,
                model: {},
                purchases: {},
                groupPurchases: "",
                supliers: {},
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
                axios.get("/api/user").then(({ data }) => {
                    this.user = data.user;
                });
            },
            getResults(page = 1) {
                axios.get("/api/purchases?page=" + page)
                .then(response => {
                    this.purchases = response.data;
                });
            },

            loadPurchase() {
                if (this.is_busy) return;
                this.is_busy = true;
                axios.get("/api/purchases", { params: this.filterForm })
                .then(({ data }) => {
                    this.purchases = data;
                })
                .finally(() => {
                    this.is_busy = false;
                });
            },

            onFilterSubmit()
            {
                this.loadPurchase();
                this.getProduct();
                this.getUser();
                this.$refs.filter.hide();
            },

            getProduct() {
                axios.get("/api/inventory")
                .then(({ data }) => {
                    this.products = data.data;
                });
            },

            deletePurchase(id) {
                Swal.fire({
                    title: "Are you sure?",
                    text: "You won't be able to revert this!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Yes, delete it!"
                })
                .then(result => {
                    if (result.value) {
                        this.form.delete("/api/purchases/" + id)
                        .then((response) => {
                            if(response.data=="You cannot delete this purchase detail because the product has decreased in the Inventory"){
                                Swal.fire(
                                    "Error!",
                                    "You cannot delete this purchase detail because the product has decreased in the Inventory",
                                    "Warning"
                                );
                            }
                            else{
                                Swal.fire(
                                    "Deleted!",
                                    "Purchase Record has been deleted.",
                                    "success"
                                );
                            }
                           

                            this.loadPurchase();
                            this.getProduct();
                        })

                        .catch(() => {
                            Swal(
                                "Failed!",
                                "Ops, Something went wrong, try again.",
                                "Warning"
                            );
                        });
                    }
                });
            }
        }
    };
</script>
