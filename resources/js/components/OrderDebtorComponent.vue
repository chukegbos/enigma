<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container mt-4">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <h4>Debtors</h4>
                    </div>
                </div>
               
                <div v-if="report_items.data.length" style="text-align:center">
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover">
                            <tr>
                                <th>Invoice ID</th>
                                <th>Customer</th>
                                <th>Phone</th>
                                <th>Address</th>
                                <!--<th>Amount Owed</th>-->
                                <th>Date</th>
                            </tr>

                            <tr
                                v-for="order in report_items.data"
                                :key="order.id"
                            >
                                <td>
                                    {{ order.sale_id }} <br> <a href="#" @click=viewItems(order.sale_id)>History</a>
                                </td>
                                <td>{{ order.customer }}</td>
                                <td>{{ order.phone }}</td>
                                <td>{{ order.address }}</td>
                                <!--<td>{{ order.amount }}</td>-->
                                <td>{{ order.updated_at | myDate }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="card-footer">
                        <pagination :data="report_items" @pagination-change-page="getResults">
                        </pagination>
                    </div>
                </div>

                <div v-else class="p-5">
                    <div class="alert alert-info text-center">
                        No Debt
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
            this.loadSales();
        },

        data() {
            return {
                filterForm: {
                    start_date: moment().subtract(30, 'days').format("YYYY-MM-DD"),
                    end_date: moment().format("YYYY-MM-DD"),
                    store: 0,
                    mode_of_payment: 0,
                },

                payments: [
                  { value: 'Cash', text: 'Cash' },
                  { value: 'POS', text: 'POS' },
                  { value: 'Transfer', text: 'Transfer' },
                ],
                stores: {},
                is_busy: false,
                report_items: null,
                summary: null,
                report_items: {
                    data: '',
                },
            };
        },

        methods: {
            getResults(page = 1) {

                axios.get('/api/store/debtors?page=' + page, { params: this.filterForm })
                .then(response => {
                    this.report_items = response.data.report_data;
                    this.summary = response.data.summary;
                });
            },

            loadSales() {
                if(this.is_busy) return;
                this.is_busy = true;

                axios.get('/api/store/debtors', { params: this.filterForm })
                .then((response) => {
                    this.report_items = response.data.report_data;
                    this.summary = response.data.summary;
                })

                .catch((err) => {
                    console.log(err);
                })

                .finally(() => {
                    this.is_busy = false;
                });
            },

            onFilterSubmit()
            {
                this.loadSales();
                this.$refs.filter.hide();
            },

            viewItems(sale_id) {
                this.$router.push({
                    path: "/store/debtors/" + sale_id
                });
            },
        },
    };
</script>

<style>
    .list_product{
        list-style: none;
    }
</style>
