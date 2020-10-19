<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container">
            <div class="row">
                <div class="col-md-6 mb-4">
                    <b-button variant="outline-primary" size="sm" v-b-modal.filter-modal><i class="fa fa-filter"></i> Filter</b-button>
                    <b-modal id="filter-modal" ref="filter" title="Filter" hide-footer>
                        <b-form @submit.stop.prevent="onFilterSubmit">
                            <b-form-group label="Store:" v-if="user.role==3">
                                <b-form-select
                                    v-model="filterForm.store" :options="stores" value-field="id" text-field="name">
                                    <template v-slot:first>
                                        <b-form-select-option :value="0">
                                            All
                                        </b-form-select-option>
                                    </template>
                                </b-form-select>
                            </b-form-group>

                            <b-form-group label="Date:" label-for="Date">
                                <b-form-datepicker v-model="filterForm.due_date" placeholder="Date"
                                    :date-format-options="{ year: 'numeric', month: 'short', day: '2-digit', weekday: 'short' }">
                                </b-form-datepicker>
                            </b-form-group>

                            <b-form-group label="Category:">
                                <b-form-select
                                    v-model="filterForm.category"
                                    :options="categories"
                                    value-field="id"
                                    text-field="name"
                                >
                                    <template v-slot:first>
                                        <b-form-select-option :value="0">
                                            All
                                        </b-form-select-option>
                                    </template>
                                </b-form-select>
                            </b-form-group>
                            <b-button type="submit" variant="primary">Search</b-button>
                        </b-form>
                    </b-modal>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">
                                Expenditures
                            </div>
                            <div class="card-tools">
                                <button class="btn btn-success" @click="newModal">
                                    Add Expenditure
                                    <i class="fa fa-plus fa-fw"></i>
                                </button>
                            </div>
                        </div>
                        <div v-if="accounts.data.length">
                            <div class="card-body table-responsive p-0">
                                <table class="table table-hover">
                                    <tr>
                                        <th>ID</th>
                                        <th>Store</th>
                                        <th>Category</th>
                                        <th>User</th>
                                        <th>Purpose</th>
                                        <th>Amount(&#8358;)</th>
                                        <th>Date</th>
                                        <th>Modify</th>
                                    </tr>

                                    <tr
                                        v-for="account in accounts.data"
                                        :key="account.id"
                                    >
                                        <td>{{ account.ref_id }}</td>
                                        <td>{{ account.store }}</td>
                                        <td>{{ account.category }}</td>
                                        <td>{{ account.user_id }}</td>
                                        <td>{{ account.purpose }}</td>
                                        <td>{{ account.amount }}</td>
                                        <td>{{ account.main_date | myDate}}</td>
                                        <td>
                                            <button class="btn btn-danger" @click.prevent="deleteAccount(account.id)">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="card-footer">
                                <pagination :data="accounts" @pagination-change-page="getResults"></pagination>
                            </div>
                        </div>

                        <div v-else class="p-5">
                            <div class="alert alert-info text-center">
                                No Expenditure.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div
                class="modal fade"
                id="addNewaccount"
                tabindex="-1"
                role="dialog"
                aria-labelledby="addNewaccountLabel"
                aria-hidden="true"
            >
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addNewaccountLabel">
                                Add New
                            </h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form @submit.prevent="createAccount()">
                            <div class="modal-body">
                                <div class="form-group">
                                    <label>Select Category</label>
                                    <b-form-select
                                        v-model="form.category"
                                        :options="categories"
                                        value-field="id"
                                        text-field="name"
                                    >
                                    <template v-slot:first>
                                        <b-form-select-option :value="null" disabled>
                                            -- Please select category--
                                        </b-form-select-option>
                                    </template>
                                    </b-form-select>
                                </div>

                                <div class="form-group">
                                    <label>Purpose</label>
                                    <input v-model="form.purpose" type="text" class="form-control":class="{'is-invalid': form.errors.has('purpose')}"/>
                                    <has-error :form="form" field="purpose"></has-error>
                                </div>

                                <div class="form-group">
                                    <label>Amount</label>
                                    <input v-model="form.amount" type="number" class="form-control":class="{'is-invalid': form.errors.has('amount')}"/>
                                    <has-error :form="form" field="amount"></has-error>
                                </div>

                                <div class="form-group">
                                    <label>Date</label>
                                    <input v-model="form.main_date" type="date" class="form-control" :class="{'is-invalid': form.errors.has('main_date')}"/>
                                    <has-error :form="form" field="main_date"></has-error>
                                </div>
                                
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-dismiss="modal">
                                    Close
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    Add
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </b-overlay>
</template>

<script>
    export default {
        data() {
            return {
                is_busy: false,
                editMode: false,
              
                model: {},
              
                accounts: [],
                categories: [],
                stores: [],
                user: "",

                form: new Form({
                    id: "",
                    purpose: "",
                    category: null,
                    amount: '',
                    main_date: '',
                }),

                accounts: {
                    data: '',
                },

                filterForm: {
                    category: 0,
                    store: 0,
                    due_date: ''
                },
            };
        },

        created() {
            this.loadAccount();
            this.getUser();
        },

        methods: {
            getUser() {
                axios.get("/api/user")
                .then(({ data }) => {
                    this.user = data.user;
                });
            },

            newModal() {
                (this.editMode = false), this.form.reset();
                $("#addNewaccount").modal("show");
            },

            getResults(page = 1) {
                axios.get("/api/account/expenditure?page=" + page).then(response => {
                    this.accounts = response.data;
                });
            },

            loadAccount() {
                if (this.is_busy) return;
                this.is_busy = true;
                
                axios.get("/api/account/expenditure", { params: this.filterForm })
                .then(({ data }) => {
                    this.accounts = data.accounts;
                    this.categories = data.categories;
                    this.stores = data.stores;
                })
                .finally(() => {
                    this.is_busy = false;
                });
            },

            onFilterSubmit()
            {
                this.loadAccount();
                this.getUser();
                this.$refs.filter.hide();
            },

            createAccount() {
                console.log(this.form);
                axios.post("/api/account/expenditure", this.form)
                .then(() => {
                    Swal.fire(
                        "Created!",
                        "Expenditure Created Successfully.",
                        "success"
                    );
                    $("#addNewaccount").modal("hide");
                    this.loadAccount();
                    this.getUser();
                })

                .catch(error => {
                    console.log(error.response.data.error);
                    if (
                        error.response.data.error == "account Already Exist"
                    ) {
                        Swal.fire(
                            "Failed!",
                            "account Already Exist",
                            "error"
                        );
                    }
                });
            },

            deleteAccount(id) {
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
                        this.$Progress.start();
                        this.form.delete("/api/account/expenditure/" + id)
                        .then(() => {
                            Swal.fire(
                                "Deleted!",
                                "Your file has been deleted.",
                                "success"
                            );
                            this.$Progress.finish();
                            this.loadAccount();
                            this.getUser();
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
        },
    };
</script>
