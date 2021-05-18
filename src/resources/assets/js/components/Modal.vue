
<template>
    <transition name="modal">
        <div class="modal-mask">
            <div class="modal-wrapper">
                <div class="modal-container">
                    <div class="modal-header">
                        <h3>{{ trans.add_message }}</h3>
                        <a href="#"
                          @click="$emit('close')"
                        >X</a>
                    </div>

                    <div class="modal-body">
                        <div class="form-group">
                            <label class="control-label" for="email">
                                {{ trans.with_who }}
                            </label>
                            <select v-model="who" class="form-control">
                                <option value="provider">{{ trans.provider }}</option>
                                <option value="user">{{ trans.user }}</option>
                            </select>
                        </div>

                        <div class="form-group">
                          <autocomplete
                            :source="getWhoUrlString()"
                            method="get"
                            input-class="form-control"
                            name="receiver_id"
                            :placeholder="trans.search"
                            results-property="users"
                            :results-display="formattedAutocomplete"
                            @selected="selectReceiver"
                            @clear="clearSelectReceiver"
                          ></autocomplete>
                        </div>

                        <div class="form-group">
                            <label class="control-label" for="email">
                                {{ trans.location }}
                            </label>
                            <select v-model="location_id" class="form-control">
                                <option value=''>{{ trans.select }}</option>
                                <option 
                                  v-for="(item, index) in locations"
                                  :key="index"
                                  :value="item.id"
                                >
                                  {{ item.name }}
                                </option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="control-label" for="email">
                                {{ trans.message_type }}
                            </label>
                            <select v-model="selectedMessageIndex" class="form-control">
                                <option value=''>{{ trans.select }}</option>
                                <option 
                                  v-for="(item, index) in canonical_messages"
                                  :key="index"
                                  :value="index"
                                >
                                  {{ item.message }}
                                </option>
                            </select>
                        </div>

                    </div>
                    
                    <div class="modal-footer">            
                       <div class="message-footer chat-send-message-footer">
                          <input @change="onFileChange" ref="picture" type="file" hidden>
                          <a @click="attachmentPicture" class="chat-attachment" href="#">
                              <i class="mdi mdi-attachment"></i>
                          </a>
                          <input v-model="messageText" type="text" :placeholder="trans.send_message">
                          <a class="chat-send-btn" v-if="messageText != ''" href="#" @click="beforeSend">
                            <i class="mdi mdi-send"></i>
                          </a>
                      </div>                    
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>

<script>
import autocomplete from 'vuejs-auto-complete';
import axios from 'axios';

export default {
  props: [
    'canonicalMessages',
    'user',
    'locations',
    'trans'
  ],
  components: {
    autocomplete
  },
  data() {
    return {
      canonical_messages: [],
      selectedMessageIndex: '',
      who: 'provider',
      receiver_id: '',
      messageText: '',
      location_id: '',
      id: '',
      token: '',
      picture: null
    }
  },
  methods: {
    beforeSend() {
      if (this.receiver_id && this.receiver_id != '')
        this.sendMessage();
      else
        this.sendBulkMessage();
    },
    async sendMessage() {
      try {

        let dataForm = new FormData();

        dataForm.append('id', this.id);
        dataForm.append('token', this.token);
        dataForm.append('receiver', this.receiver_id);
        dataForm.append('message', this.messageText);
        dataForm.append('location_id', this.location_id);

        if (this.picture)
          dataForm.append('picture', this.picture);

        await axios.post('/api/libs/set_direct_message', dataForm);
        window.location.reload();
      } catch (error) {
        console.log('sendMessage', error);
      }
    },
    async sendBulkMessage() {
      try {
        let dataForm = new FormData();

        dataForm.append('id', this.id);
        dataForm.append('token', this.token);
        dataForm.append('message', this.messageText);
        dataForm.append('location_id', this.location_id);
        dataForm.append('type', this.who);

        if (this.picture)
          dataForm.append('picture', this.picture);

        await axios.post('/api/libs/admin_bulk_message', dataForm);
        window.location.reload();
      } catch (error) {
        console.log('sendBulkMessage', error);
      }
    },
    getWhoUrlString() {
      return `/admin/lib/api/get_user?type=${this.who}&name=`;
    },
    selectedMessage() {
      if (this.selectedMessageIndex !== '') {
        const message = this.canonical_messages[this.selectedMessageIndex];
        this.messageText = message.message;
      }
    },
    formattedAutocomplete (result) {
			return result.name;
		},
    selectReceiver (result) {
			const { selectedObject } = result;
			this.receiver_id = selectedObject.id;
    },
    clearSelectReceiver () {
			this.receiver_id = '';
		},
    attachmentPicture() {
        this.$refs.picture.click();
    },
    onFileChange(e) {
        this.picture = e.target.files[0];
        this.$toasted.show(
          'Imagem selecionada com sucesso!', 
          { 
              theme: "bubble", 
              type: "info" ,
              position: "bottom-center", 
              duration : 5000
          }
        );
    }
  },
  watch: {
    selectedMessageIndex: function() {
      this.selectedMessage();
    }
  },
  created() {
    this.canonical_messages = this.canonicalMessages;
    
    if (this.user) {
      this.id = this.user.id;
      this.token = this.user.id;
    }
  }
}

</script>

<style>
.modal-mask {
  position: fixed;
  z-index: 9998;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, .5);
  display: table;
  transition: opacity .3s ease;
}

.modal-wrapper {
  display: table-cell;
  vertical-align: middle;
}

.modal-container {
  width: 100%;
  max-width: 500px;
  margin: 0px auto;
  background-color: #fff;
  border-radius: 2px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, .33);
  transition: all .3s ease;
  font-family: Helvetica, Arial, sans-serif;
}

.modal-header h3 {
  margin-top: 0;
}

.modal-header a {
  color: red;
  font-size: 22px;
}

.modal-default-button {
  float: right;
}

.modal-enter {
  opacity: 0;
}

.modal-leave-active {
  opacity: 0;
}

.modal-enter .modal-container,
.modal-leave-active .modal-container {
  -webkit-transform: scale(1.1);
  transform: scale(1.1);
}

.message-footer {
  width: 100%;
}
</style>