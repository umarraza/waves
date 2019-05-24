<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;

class VerifyJWTToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // return $next($request);
        $response = [
            'data' => [
                'error' => 400,
                'message' => 'Invalid Request.',
            ],
            'status' => false
        ];
         try{
            $user = JWTAuth::toUser($request->input('token'));
        }catch (JWTException $e) {
            if($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                // return response()->json(['token_expired'], $e->getStatusCode());
                $response['data']['error'] = $e->getStatusCode();
                $response['data']['message'] = 'Token Expired';
                return response()->json($response);
            }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
                // return response()->json(['token_invalid'], $e->getStatusCode());
                $response['data']['error'] = $e->getStatusCode();
                $response['data']['message'] = 'Token Invalid';
                return response()->json($response);
            }else{
                // return response()->json(['error'=>'Token is required']);
                $response['data']['error'] = $e->getStatusCode();
                $response['data']['message'] = 'Token is Expired';
                return response()->json($response);
            }
        }
        catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            // return response()->json('token_expired');
            $response['data']['error'] = $e->getStatusCode();
            $response['data']['message'] = 'Token Expired';
            return response()->json($response);

        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            // return response()->json('token_invalid');
            $response['data']['error'] = $e->getStatusCode();
            $response['data']['message'] = 'Token Invalid';
            return response()->json($response);

        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            // return response()->json('token_absent');
            $response['data']['error'] = $e->getStatusCode();
            $response['data']['message'] = 'Token Not Found';
            return response()->json($response);
        }
       return $next($request);
    }
}
