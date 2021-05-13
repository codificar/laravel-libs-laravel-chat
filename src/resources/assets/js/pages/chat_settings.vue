<template>
    <div>
        <div class="col-lg-12">
        <div class="card card-outline-info">
            <!--/ CARD -->
            <div class="card-header">
                <h4 slot="card-title" class="m-b-0 text-white">{{ trans('laravelchat.config') }}</h4>
            </div>
            <!--/ Content -->
            <div class="card-block">
                <div class="box box-warning">
                    <div class="box-header">
                        
                    </div>
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>
                                    {{ trans('laravelchat.set_default_admin') }}
                                </label>           
                                <select v-model="default_admin" @change="setDefaultAdmin" class="form-control">
                                    <option v-for="item in adminsData" :key="item.id" :value="item.id">
                                        {{ item.username }}
                                    </option>
                                </select>	
                            </div>
                        </div>
                    </div>    
                </div>
            </div>
        </div>
        </div>
    </div>


</template>

<script>
import axios from 'axios';

export default {    
    props: [
        "Admins",
        "DefaultAdmin"
    ],
    data() {
        return {
            default_admin: '',
            adminsData: []
        }
    },
    methods: {
        async setDefaultAdmin() {
            try {
                const response = await axios.post('/admin/lib/api/set_default_admin', {
                    id: this.default_admin
                });

                const { data } = response;

                if (data.success) {
                    this.$toasted.show(
                        'Salvo com succeso', 
                        { 
                            theme: "bubble", 
                            type: "success" ,
                            position: "bottom-center", 
                            duration : 5000
                        }
                    );
                } else {
                    this.$toasted.show(
                        'Erro ao salvar', 
                        { 
                            theme: "bubble", 
                            type: "danger" ,
                            position: "bottom-center", 
                            duration : 5000
                        }
                    );
                }
            } catch (error) {
                console.log('setDefaultAdmin', error);
                this.$toasted.show(
                    'Erro ao salvar', 
                    { 
                        theme: "bubble", 
                        type: "danger" ,
                        position: "bottom-center", 
                        duration : 5000
                    }
                );
            }
        }
    },
    created() {
        this.default_admin = this.DefaultAdmin;
        this.adminsData = this.Admins;
    }
}
</script>

<style>

</style>