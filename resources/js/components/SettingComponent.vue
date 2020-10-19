<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container">
            <div class="row" v-if="view_error">
                <div class="col-md-12">
                    <b-alert show dismissible variant="danger">
                        {{ view_error }}
                    </b-alert>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">App Setting</h3>
                        </div>

                        <form @submit.prevent="updateSite()">
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Sitename</label>
                                    <input v-model="form.sitename" type="text" name="sitename" class="form-control" required/>
                                </div>

                                <div class="form-group">
                                    <label>Email Address</label>
                                    <input v-model="form.email" type="email" name="email" class="form-control"/>
                                </div>

                                <div class="form-group">
                                    <label>Phone Number</label>
                                    <input v-model="form.phone" type="tel" class="form-control"/>
                                </div>

                                <div class="form-group">
                                    <label>Address</label>
                                    <input v-model="form.address" type="text" class="form-control"/>
                                </div>

                                <div class="form-group">
                                    <label>Admin Sale Rate (%)</label>
                                    <input v-model="form.admin_percent" type="number" class="form-control" required/>
                                </div>

                                <div class="form-group">
                                    <label>Manager Sale Rate (%)</label>
                                    <input v-model="form.manager_percent" type="number" class="form-control" required/>
                                </div>

                                <div class="form-group">
                                    <label>Cashier Sale Rate (%)</label>
                                    <input v-model="form.cashier_percent" type="number" class="form-control" required/>
                                </div>

                                <div class="form-group">
                                    <label>1 Chinese Yuan</label>
                                    <input v-model="form.naira_value" type="text" class="form-control" required/>
                                </div>

                            </div>

                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">
                                    Submit
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
                form: new Form({
                    sitename: '',
                    email: '',
                    phone: '',
                    address: '',
                    admin_percent: '',
                    manager_percent: '',
                    cashier_percent: '',
                    naira_value: '',
                }),
                view_error: '',
            };
        },

        created() {
            this.loadSite();
        },

        methods: {
            loadSite() {
                axios.get("/api/setting")
                .then(({ data }) => {
                    this.form = data;
                });
            },

            updateSite() {
                axios.put("/api/setting/" + this.form.id, this.form)
                .then(() => {
                    Swal.fire("Updated!", "Site Updated Successfully.", "success");
                    this.loadSite();
                })
                .catch((data) => {
                    this.view_error = data;
                });
            },
        },

        
    };
</script>
