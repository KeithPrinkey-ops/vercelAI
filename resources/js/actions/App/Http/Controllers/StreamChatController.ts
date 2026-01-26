import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\StreamChatController::__invoke
* @see app/Http/Controllers/StreamChatController.php:33
* @route '/api/chat'
*/
const StreamChatController = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: StreamChatController.url(options),
    method: 'post',
})

StreamChatController.definition = {
    methods: ["post"],
    url: '/api/chat',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\StreamChatController::__invoke
* @see app/Http/Controllers/StreamChatController.php:33
* @route '/api/chat'
*/
StreamChatController.url = (options?: RouteQueryOptions) => {
    return StreamChatController.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\StreamChatController::__invoke
* @see app/Http/Controllers/StreamChatController.php:33
* @route '/api/chat'
*/
StreamChatController.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: StreamChatController.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\StreamChatController::__invoke
* @see app/Http/Controllers/StreamChatController.php:33
* @route '/api/chat'
*/
const StreamChatControllerForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: StreamChatController.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\StreamChatController::__invoke
* @see app/Http/Controllers/StreamChatController.php:33
* @route '/api/chat'
*/
StreamChatControllerForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: StreamChatController.url(options),
    method: 'post',
})

StreamChatController.form = StreamChatControllerForm

export default StreamChatController