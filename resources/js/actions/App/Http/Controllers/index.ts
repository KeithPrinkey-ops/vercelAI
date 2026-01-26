import StreamChatController from './StreamChatController'
import Settings from './Settings'

const Controllers = {
    StreamChatController: Object.assign(StreamChatController, StreamChatController),
    Settings: Object.assign(Settings, Settings),
}

export default Controllers