/**
 * The HTTP response data of the API request
 */
export interface HttpResponseData<T> {
  data: T;
  message?: string;
}

/**
 * The query parameters of the API request (where, with, withCount...)
 */
export interface QueryParams<T> {
  where?: {
    [K in keyof T]?: T[K] | T[K][];
  };
  with?: string;
  withCount?: string;
  sort?: string;
}

/**
 * The optional query parameters of the API request (where, with, withCount...)
 */
export type OptionalQueryParams<T> = QueryParams<T> | null;
